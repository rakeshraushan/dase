<?php

class Dase_ModuleHandler_Itsprop extends Dase_Handler {

	public $resource_map = array(
		'/' => 'home',
		'index' => 'home',
	);

	public function setup($r)
	{
	}

	public function getHome($r) 
	{
		//$user = $r->getUser();
		$tpl = new Dase_Template($r,true);
		$tpl->assign('home',Dase_Atom_Feed::retrieve(APP_ROOT. "/search.atom?itsprop~title=homepage"));
		$r->renderResponse($tpl->fetch('home.tpl'));
	}

}
