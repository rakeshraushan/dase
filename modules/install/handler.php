<?php

class Dase_ModuleHandler_Install extends Dase_Handler {

	public $db_set;
	public $resource_map = array(
		'/' => 'info',
		'index' => 'info',
		'index/{msg}' => 'info',
		'dbchecker' => 'dbchecker',
	);

	public function setup($request)
	{
		if (!is_writeable(CACHE_DIR)) {
			$html = "<html><body>";
			$html .= "<h3>".CACHE_DIR." directory must be writeable by the web server</h3>";
			if (!is_writeable(DASE_LOG)) {
				$html .= "<h3>".DASE_LOG." directory must be writeable by the web server for logging to be enabled</h3>";
			}
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
		//file_put_contents(DASE_PATH.'/local_config.php',$file_contents);
		$tpl = new Dase_Template($request,true);
		$tpl->assign('conf',Dase_Config::getAll());
		$request->renderResponse($tpl->fetch('index.tpl'),false);
	}

	public function done($request) 
	{
		//setup method determined we are good to go
		$request->renderRedirect();
	}

	public function postToDbchecker($request) 
	{
		$name = $request->get('db_name');
		$path = $request->get('db_path');
		$type = $request->get('db_type');
		$host = $request->get('db_host');
		$user = $request->get('db_user');
		$pass = $request->get('db_pass');
		$driverOpts = array();
		if ('sqlite' == $type) {
			$dsn = "sqlite:".$path;
		} else {
			$dsn = $type . ":host=$host;dbname=".$name;
		}
		try {
			$db = new PDO($dsn, $user, $pass, $driverOpts);
		} catch (PDOException $e) {
			$request->renderResponse('no|connect failed: ' . $e->getMessage(),false);
		}
		try {
			if (count(Dase_DB::listTables())) {
				$request->renderResponse("ok|Database connection was successful ($count tables exist)",false);
			}
		} catch (PDOException $e) {
			//if we are here, passed in settings worked, but Dase_Config settings (saved settings) did not
		}
		//since connection, not table count, is dependent on passed in settings
		$request->renderResponse("ready|Database connection was successful.",false);
	}
}
