<?php

class Dase 
{
	public $request;
	private $config;
	private $log;

	public static function createApp($base_path)
	{
		$c = new Dase_Config($base_path);
		$c->load('inc/config.php');
		$c->load('inc/local_config.php');

		//imagemagick binary, the one & only global
		define('CONVERT',$c->getAppSettings('convert'));

		$r = new Dase_Http_Request($base_path);
		$log = new Dase_Log($c->getLogDir(),'dase.log',Dase_Log::DEBUG);
		$cookie = new Dase_Cookie($r->app_root,$r->module,$c->getAuth('token'));
		$cache = Dase_Cache::get($c->getCacheType(),$c->getCacheDir());
		$db = new Dase_DB($c->get('db'),$log);
		$user = new Dase_DBO_DaseUser($db);
		$user->setAuth($c->getAuth());
		$r->setAuth($c->getAuth());
		$r->store('user',$user);
		$r->store('config',$c);
		$r->store('cookie',$cookie);
		$r->store('cache',$cache);
		$r->store('db',$db);
		$r->store('log',$log);

		$r->initPlugin($c->getCustomHandlers());

		$app = new Dase;
		$app->config = $c;
		$app->request = $r;
		$app->log = $log;
		return $app;
	}

	public function run()
	{
		$c = $this->config;
		$log = $this->log;
		$r = $this->request;

		if (!$r->handler) {
			$r->renderRedirect($c->getAppSettings('default_handler'));
		}

		$log->debug("\n-----------------\n".$r->getLogData()."-----------------\n");
		$classname = '';
		if ($r->module) {
			//modules, by convention, have one handler in a file named
			$handler_file = $r->base_path.'/modules/'.$r->module.'/handler.php';
			if (file_exists($handler_file)) {
				include "$handler_file";
				$c->set('module',$r->module);

				//module can set/override configurations
				$handler_config_file = $r->base_path.'/modules/'.$r->module.'/inc/config.php';
				$c->load($handler_config_file);

				//modules can carry their own libraries
				$new_include_path = ini_get('include_path').':modules/'.$r->module.'/lib'; 
				ini_set('include_path',$new_include_path); 

				//would this allow module names w/ underscores???
				//$classname = 'Dase_ModuleHandler_'.Dase_Util::camelize($r->module);
				$classname = 'Dase_ModuleHandler_'.ucfirst($r->module);
			} else {
				$r->renderError(404,"no such handler: $handler_file");
			}
		} else {
			$classname = 'Dase_Handler_'.Dase_Util::camelize($r->handler);
		}
		if (class_exists($classname,true)) {
			$handler = new $classname($r->retrieve('db'),$c->getMediaDir());
			$handler->dispatch($r);
		} else {
			$r->renderError(404,'no such handler class'); //will NOT be logged
		}
	}
}
