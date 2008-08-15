<?php

class Dase_ModuleHandler_Install extends Dase_Handler {

	public $resource_map = array(
		'/' => 'info',
		'index' => 'info',
		'index/{msg}' => 'info',
	);

	public function setup($request)
	{
		$u = new Dase_DBO_DaseUser;
		if (!$u->findOne()) {
			$this->createUser();
		}
	}

	public function getInfo($request) 
	{
		$conf = var_export(Dase_Config::getAll(),true);
		$file_contents = "<?php \$conf=$conf;";
		//file_put_contents(DASE_PATH.'/local_config.php',$file_contents);
		$tpl = new Dase_Template($request,true);
		$tpl->assign('conf',Dase_Config::getAll());
		$request->renderResponse($tpl->fetch('index.tpl'));
	}

	public function createUser($request) 
	{
		$tpl = new Dase_Template($request,true);
		$request->renderResponse($tpl->fetch('user_form.tpl'));
	}
}
