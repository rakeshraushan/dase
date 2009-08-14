<?php

class Dase_Handler_Help extends Dase_Handler
{
	public $resource_map = array(
		'/' => 'help',
	);

	protected function setup($r)
	{
	}

	public function getHelp($r) {
		$t = new Dase_Template($r);
		$t->assign('collection',Dase_Atom_Feed::retrieve($r->app_root.'/collection/dase_help.atom?limit=100'));
		$r->renderResponse($t->fetch('help.tpl'));
	}
}

