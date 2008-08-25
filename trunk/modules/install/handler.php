<?php

class Dase_ModuleHandler_Install extends Dase_Handler {

	public $db_set;
	public $resource_map = array(
		'/' => 'info',
		'index' => 'info',
		'index/{msg}' => 'info',
		'dbchecker' => 'dbchecker',
		'savesettings' => 'savesettings',
		'dbinit' => 'dbinit',
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

	public function postToPathchecker($request) 
	{
		$resp = '';
		if (is_writeable($request->get('path_to_media'))) {
			$resp = "msg_ready|This path is writeable|";
		} else {
			$resp = "msg_no|This path is NOT writeable|";
		}
		$request->renderResponse($resp);
	}

	public function postToDbchecker($request) 
	{
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
			$request->renderResponse('no|connect failed: ' . $e->getMessage());
		}
		try {
			$count = count(Dase_DB::listTables());
			if ($count) {
				$request->renderResponse("ok|Database connection was successful ($count tables exist)");
			}
		} catch (PDOException $e) {
			//if we are here, passed in settings worked, but Dase_Config settings (saved settings) did not
		}
		//since connection, not table count, is dependent on passed in settings
		$request->renderResponse("ready|Database connection was successful.");
	}

	public function postToSavesettings($request) 
	{
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
			$request->renderResponse('no|connect failed: ' . $e->getMessage());
		}
		try {
			$count = count(Dase_DB::listTables());
			if ($count) {
				$request->renderResponse("ok|Database is already initialized ($count tables exist)");
			}
		} catch (PDOException $e) {
			//if we are here, passed in settings worked, but Dase_Config settings (saved settings) did not
		}
		//since connection, not table count, is dependent on passed in settings
		$tpl = new Dase_Template($request,true);
		$tpl->assign('eid',$request->get('eid'));
		$tpl->assign('password',$request->get('password'));
		$tpl->assign('path_to_media',$request->get('path_to_media'));
		$tpl->assign('convert_path',$request->get('convert_path'));
		$tpl->assign('db',$db);
		$tpl->assign('token',md5(time().'abc'));
		$tpl->assign('ppd_token',md5(time().'def'));
		$tpl->assign('db',$db);
		$config = $tpl->fetch('local_config.tpl');
		$lc = DASE_PATH.'/inc/local_config.php';
		if (!file_exists($lc)) {
			$request->renderResponse(
				'display|Save the following text as '.$lc.
				', then click "confirm settings" button:|'.$config
			);
		} else {
			$request->renderResponse('ready|Settings OK! Please initialize the database');
		}
		exit;
	}

	public function postToDbinit($request) 
	{
		include (DASE_PATH.'/modules/install/mysql_schema.php');
		Dase_DB::query($query)->fetchAll();
		$u = new Dase_DBO_DaseUser;
		$u->eid = $request->get('eid');
		$u->name = $request->get('eid');
		$u->insert();
		$request->setUser($u);
		$count = count(Dase_DB::listTables());
		Dase_Cache_File::expunge();

		$url = "http://quickdraw.laits.utexas.edu/dase1/collection/germanic";
		$feed = Dase_Atom_Feed::retrieve($url.'?format=atom');
		$coll_ascii_id = $feed->getAsciiId();
		$feed->ingest($request);
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
