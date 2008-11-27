<?php

class Dase_ModuleHandler_Jsoneditor extends Dase_Handler {

	public $resource_map = array(
		'/' => 'editor',
		'index' => 'editor',
		'editor' => 'editor',
	);

	public function getEditor($r) 
	{
		$tpl = new Dase_Template($r,true);
		$r->renderResponse($tpl->fetch('editor.tpl'));
	}
}
