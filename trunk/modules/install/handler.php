<?php

class Dase_ModuleHandler_Install extends Dase_Handler {

	public $db_set;
	public $resource_map = array(
		'/' => 'info',
		'index' => 'info',
		'index/{msg}' => 'info',
		'config_checker' => 'config_checker',
		'savesettings' => 'savesettings',
		'dbinit' => 'dbinit',
		'dbsetup' => 'dbsetup',
		'pathchecker' => 'pathchecker',
	);

	public function setup($request)
	{
		if (!is_writeable(CACHE_DIR)) {
			$html = "<html><body>";
			$html .= "<h3>".CACHE_DIR." directory must be writeable by the web server</h3>";
			$html .= "</body></html>";
			echo $html;
			exit;
		}
		if (!is_writeable(DASE_LOG)) {
			$html = "<html><body>";
			$html .= "<h3>".DASE_LOG." file must be writeable by the web server for logging</h3>";
			$html .= "</body></html>";
			echo $html;
			exit;
		}
		try {
			//see if db is set and we have users
			$u = new Dase_DBO_DaseUser;
			if ($u->findOne()) {
				//if so, make sure user is logged in
				//and is a superuser
				$user = $request->getUser();
				if ($user->isSuperuser()) {
					$this->done($request);
				} else {
					$request->renderError(401);
				}
			}
		} catch (Exception $e) {
			$this->db_set = 0;
		}
	}

	public function getInfo($request) 
	{
		$conf = var_export(Dase_Config::getAll(),true);
		$file_contents = "<?php \$conf=$conf;";
		$tpl = new Dase_Template($request,true);
		$conf = Dase_Config::getAll();
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
		$lc = DASE_PATH.'/inc/local_config.php';
		$tpl->assign('lc',$lc);
		$request->renderResponse($tpl->fetch('index.tpl'));
	}

	public function done($request) 
	{
		//setup method determined we are good to go
		$request->renderRedirect();
	}

	public function postToConfigChecker($request) 
	{
		$resp = array();
		if (is_writeable($request->get('path_to_media'))) {
			$resp['path'] = 1;
		} else {
			$resp['path'] = 0;
		}

		$resp['db'] = 1;

		$db = array();
		$db['name'] = $request->get('db_name');
		$db['path'] = $request->get('db_path');
		$db['type'] = $request->get('db_type');
		$db['host'] = $request->get('db_host');
		$db['user'] = $request->get('db_user');
		$db['pass'] = $request->get('db_pass');
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
		}
		if ($resp['db'] && $resp['path']) {
			$tpl = new Dase_Template($request,true);
			$tpl->assign('main_title',$request->get('main_title'));
			if ($request->get('table_prefix')) {
				$tpl->assign('table_prefix',trim($request->get('table_prefix'),'_').'_');
			}
			$tpl->assign('eid',$request->get('eid'));
			$tpl->assign('password',$request->get('password'));
			$tpl->assign('path_to_media',$request->get('path_to_media'));
			$tpl->assign('convert_path',$request->get('convert_path'));
			$tpl->assign('db',$db);
			$tpl->assign('token',md5(time().'abc'));
			$tpl->assign('ppd_token',md5(time().'def'));
			$tpl->assign('service_token',md5(time().'ghi'));
			$tpl->assign('db',$db);
			$resp['config'] = $tpl->fetch('local_config.tpl');
			if (!file_exists(DASE_PATH.'/inc/local_config.php')) {
				$resp['local_config_path'] = DASE_PATH.'/inc/local_config.php';
			} else {
				//signal here that we are ready to continue
				//$request->renderResponse('ready|Settings OK! Please initialize the database');
			}
		}
		$request->renderResponse(Dase_Json::get($resp));
	}

	public function postToDbinit($request) 
	{
		$type = $request->get('db_type');
		//todo: i need an sqlite schema as well
		$table_prefix = Dase_Config::get('table_prefix');
		//the schema uses variable $table_prefix
		include(DASE_PATH.'/modules/install/'.$type.'_schema.php');
		$db = Dase_DB::get();
		if (false !== $db->exec($query)) {
			$request->renderResponse("ok|Database has been initialized");
		} else {
			$request->renderResponse("no|Sorry, there was an error");
		}
	}

	public function postToDbsetup($request) 
	{
		$u = new Dase_DBO_DaseUser;
		$u->eid = $request->get('eid');
		$u->name = $request->get('eid');
		$u->insert();
		$request->setUser($u);
		$count = count(Dase_DB::listTables());
		Dase_Cache_File::expunge();

		$url = "http://daseproject.org/collection/sample.atom";
		$feed = Dase_Atom_Feed::retrieve($url);
		$coll_ascii_id = $feed->getAsciiId();
		$feed->ingest($request,true);
		$cm = new Dase_DBO_CollectionManager;
		$cm->dase_user_eid = $u->eid;
		$cm->collection_ascii_id = $coll_ascii_id;
		$cm->auth_level = 'superuser';
		$cm->created = date(DATE_ATOM); 
		$cm->insert();

		$login_url = APP_ROOT.'/login/form';
		if ($count) {
			$request->renderResponse("ok|Database has been initialized ($count tables created) <a href=\"$login_url\">please login</a>");
		}
		$request->renderResponse("ok|Database has been initialized");
	}
}
