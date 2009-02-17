<?php

class Dase 
{
	public $request;

	public function __construct($request)
	{
		$this->request = $request;
		$this->config = $request->retrieve('config');
		$this->log = $request->retrieve('log');
	}

	public function run()
	{
		$c = $this->config;
		$r = $this->request;
		$log = $this->log;

		if (!$r->handler) {
			$r->renderRedirect($c->getAppSettings('default_handler'));
		}

		$log->debug("\n-----------------\n".$r->getLogData()."-----------------\n");
		$classname = '';
		if ($r->module) {

			//modules, by convention, have one handler in a file named
			$handler_file = $c->get('base_path').'/modules/'.$r->module.'/handler.php';
			if (file_exists($handler_file)) {
				include "$handler_file";
				$c->set('module',$r->module);

				//module can set/override configurations
				$handler_config_file = $c->get('base_path').'/modules/'.$r->module.'/inc/config.php';
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
			$handler = new $classname();
			$handler->dispatch($r);
		} else {
			$r->renderError(404,'no such handler class');
		}
	}
}
