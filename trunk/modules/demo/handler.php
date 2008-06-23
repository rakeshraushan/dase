<?php

class Dase_ModuleHandler_Demo extends Dase_Handler {

	public $resource_map = array(
		'index' => 'demo',
		'{site_name}' => 'site',
	);

	protected function setup($request)
	{
	}

	public function getDemo($request) {
		$t = new Dase_Template($request,'demo');
		$request->renderResponse($t->fetch('example.tpl'));
	}

	public function getDemoJson($request) {
		$request->renderResponse('hello json');

	}

	public function getSite($request)
	{
		$site_name = $request->get('site_name');
	}


}
