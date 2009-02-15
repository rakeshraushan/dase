<?php

class Dase_Handler_Test extends Dase_Handler
{
	public $resource_map = array(
		'/' => 'demo',
		'demo' => 'demo',
	);

	protected function setup($r)
	{
	}

	public function getDemo($r)
	{
		$user = $r->getUser();
		$t = new Dase_Template($r);
		if ($r->has('url')) {
			$entry = Dase_Atom_Entry::retrieve($r->get('url'),$user->eid,$user->getHttpPassword());
			$t->assign('url',$r->get('url'));
			$t->assign('entry',$entry);
			$t->assign('atom_doc',$entry->asXml());
		}	
		$r->renderResponse($t->fetch('test/demo.tpl'));
	}
}
