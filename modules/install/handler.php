<?php

class Dase_ModuleHandler_Install extends Dase_Handler {

	public $db_set;
	public $resource_map = array(
		'/' => 'info',
		'index' => 'info',
		'index/{msg}' => 'info',
		'config_checker' => 'config_checker',
		'setup_tables' => 'setup_tables',
		'create_admin' => 'create_admin',
		'create_sample' => 'create_sample',
	);

	public function setup($r)
	{

		$config = $r->retrieve('config');

		if (!is_writeable($config->getCacheDir())) {
			$html = "<html><body>";
			$html .= "<h3>".$config->getCacheDir()." must be writeable by the web server.</h3>";
			$html .= "</body></html>";
			echo $html;
			exit;
		}
		if (!is_writeable($config->getLogDir())) {
			$html = "<html><body>";
			$html .= "<h3>".$config->getLogDir()." must be writeable by the web server.</h3>";
			$html .= "</body></html>";
			echo $html;
			exit;
		}
		if (!is_writeable($config->getMediaDir())) {
			$html = "<html><body>";
			$html .= "<h3>".$config->getMediaDir()." must be writeable by the web server.</h3>";
			$html .= "</body></html>";
			echo $html;
			exit;
		}
		try {
			//try to connect
			$dbh = $this->db->getDbh();
		} catch (PDOException $e) {
			//local_config.php is not setup
			$this->db_set = 0;
			return;
		}
		try {
			//see if we have users
			$u = $r->retrieve('user');
			if ($u->findOne()) {
				//if so, make sure user is logged in
				//and is a superuser
				$user = $r->getUser();
				if ($user->isSuperuser()) {
					$this->done($r);
				} else {
					$r->renderError(401);
				}
			} else {
				//if we are here, means local_config.php is OK, we need to setup tables and user
				if ('info' == $r->resource) {
					$this->getSetup($r);
				}
			}
		} catch (Exception $e) {
			$this->db_set = 0;
			return;
		}
	}

	public function getSetup($r) 
	{
		$tpl = new Dase_Template($r,true);
		$r->renderResponse($tpl->fetch('setup.tpl'));
	}

	public function getInfo($r) 
	{
		$conf = var_export($r->retrieve('config')->getAll(),true);
		$file_contents = "<?php \$conf=$conf;";
		$tpl = new Dase_Template($r,true);
		$conf = $r->retrieve('config')->getAll();
		if (isset($conf['superuser'])  && is_array($conf['superuser'])) {
			$eid = array_shift(array_keys($conf['superuser']));
			$tpl->assign('eid',$eid);
			$tpl->assign('password',$conf['superuser'][$eid]);
		}
		exec('which convert',$path_array);
		if ($path_array[0]) {
			$tpl->assign('convert_path',$path_array[0]);
		}
		$tpl->assign('conf',$conf);
		$lc = $r->base_path.'/inc/local_config.php';
		$tpl->assign('lc',$lc);
		$r->renderResponse($tpl->fetch('index.tpl'));
	}

	public function done($r) 
	{
		//setup method determined we are good to go
		$r->renderRedirect();
	}

	public function postToConfigChecker($r) 
	{
		$resp = array();
		if (is_writeable($r->get('path_to_media'))) {
			$resp['path'] = 1;
		} else {
			$resp['path'] = 0;
		}
		$resp['db'] = 1;
		$resp['proceed'] = 0;

		$db = array();
		$db['name'] = $r->get('db_name');
		$db['path'] = $r->get('db_path');
		$db['type'] = $r->get('db_type');
		$db['host'] = $r->get('db_host');
		$db['user'] = $r->get('db_user');
		$db['pass'] = $r->get('db_pass');
		if ('sqlite' == $db['type']) {
			$dsn = "sqlite:".$db['path'];
		} else {
			$dsn = $db['type'].':host='.$db['host'].';dbname='.$db['name'];
		}
		try {
			$pdo = new PDO($dsn, $db['user'], $db['pass']);
		} catch (PDOException $e) {
			$resp['db'] = 0;
			$resp['db_msg'] = $e->getMessage();
			$r->logger()->debug('DBO error: '.$resp['db_mgs']);
		}
		if ($resp['db'] && $resp['path']) {
			$tpl = new Dase_Template($r,true);
			$tpl->assign('main_title',$r->get('main_title'));
			if ($r->get('table_prefix')) {
				$tpl->assign('table_prefix',trim($r->get('table_prefix'),'_').'_');
			}
			$tpl->assign('eid',$r->get('eid'));
			$tpl->assign('password',$r->get('password'));
			$tpl->assign('path_to_media',$r->get('path_to_media'));
			$tpl->assign('convert_path',$r->get('convert_path'));
			$tpl->assign('db',$db);
			$tpl->assign('token',md5(time().'abc'));
			$tpl->assign('ppd_token',md5(time().'def'));
			$tpl->assign('service_token',md5(time().'ghi'));
			$tpl->assign('db',$db);
			if (!file_exists(DASE_PATH.'/inc/local_config.php')) {
				$resp['local_config_path'] = DASE_PATH.'/inc/local_config.php';
				$resp['config'] = $tpl->fetch('local_config.tpl');
			} else {
				$resp['proceed'] = 1;
			}
		}
		$r->renderResponse(Dase_Json::get($resp));
	}

	public function postToSetupTables($r) 
	{
		$count = count(Dase_DB::listTables());
		if ($count) {
			$resp['msg'] = "$count tables already exist.";
			$resp['ok'] = 1;
			$r->renderResponse(Dase_Json::get($resp));
		}
		$resp = array();
		$dbconf = Dase_Config::get('db');
		$type = $dbconf['type'];
		//todo: i need an sqlite schema as well
		$table_prefix = Dase_Config::get('table_prefix');
		//the schema uses variable $table_prefix
		include(DASE_PATH.'/modules/install/'.$type.'_schema.php');
		$db = Dase_DB::get();
		try {
			if (false === $db->exec($query)) {
				$error_info = $db->errorInfo();
				$resp['msg'] = $error_info[2];
			} else {
				$resp['msg'] = "Database tables have been created.";
				$resp['ok'] = 1;
			}
		} catch (PDOException $e) {
			$resp['msg'] = "There was a problem creating database tables: ".$e->getMessage();
		} catch (Exception $e) {
			$resp['msg'] = "There was a problem creating database tables: ".$e->getMessage();
		}
		$r->renderResponse(Dase_Json::get($resp));
	}

	public function postToCreateAdmin($r) 
	{
		$resp = array();
		$superusers = Dase_Config::get('superuser');
		$u = new Dase_DBO_DaseUser;
		$u->eid = array_shift(array_keys($superusers));
		if ($u->findOne()) {
			$resp['msg'] = "Admin user \"$u->eid\" already exists";
			$resp['ok'] = 1;
			$r->renderResponse(Dase_Json::get($resp));
		}
		$u->name = $u->eid;
		if ($u->insert()) {
			$resp['msg'] = "Admin user \"$u->eid\" created. (See local_config.php for password)";
			$resp['ok'] = 1;
		} else {
			$resp['msg'] = "There was a problem creating admin user \"$u->eid\".";
			$resp['ok'] = 0;
		}
		$r->renderResponse(Dase_Json::get($resp));
	}

	public function postToCreateSample($r)
	{

		$resp = array();
		$url = "http://daseproject.org/collection/sample.atom";
		$feed = Dase_Atom_Feed::retrieve($url);
		$coll_ascii_id = $feed->getAsciiId();
		$feed->ingest($r,true);
		$cm = new Dase_DBO_CollectionManager;
		$cm->dase_user_eid = $u->eid;
		$cm->collection_ascii_id = $coll_ascii_id;
		$cm->auth_level = 'superuser';
		$cm->created = date(DATE_ATOM); 
		$cm->insert();

		$login_url = APP_ROOT.'/login/form';
	}
}
