<?php

class Dase_ModuleHandler_Sample extends Dase_Handler 
{
	public $resource_map = array(
		'index' => 'index',
	);

	public function setup($r)
	{
	}

	public function getIndex($r) 
	{
		$t = new Dase_Template($r,true);
		$t->assign('feed',Dase_Atom_Feed::retrieve(
			$r->app_root."/search.atom?mexican_american_experience~host=wheat"));
		$r->renderResponse($t->fetch('home.tpl'));
	}
}
