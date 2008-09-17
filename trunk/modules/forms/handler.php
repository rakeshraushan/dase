<?php

class Dase_ModuleHandler_Forms extends Dase_Handler {

	public $resource_map = array(
		'/' => 'new_form',
		'index' => 'new_form',
	);

	public function setup($r)
	{
	}

	public function getNewForm($r) 
	{
		$tpl = new Dase_Template($r,true);
		$r->renderResponse($tpl->fetch('index.tpl'));
	}
}
