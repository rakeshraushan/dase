<?php

class Dase_ModuleHandler_Forms extends Dase_Handler {

	public $resource_map = array(
		'/' => 'new_form',
		'index' => 'new_form',
	);

	public function setup($r)
	{
		$this->user = $r->getUser();
	}

	public function getNewForm($r) 
	{
		$tpl = new Dase_Template($r,true);
		$tpl->assign('user',Utlookup::getRecord($this->user->eid));
		$r->renderResponse($tpl->fetch('index.tpl'));
	}
}
