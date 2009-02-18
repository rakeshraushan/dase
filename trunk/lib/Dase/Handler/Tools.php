<?php

class Dase_Handler_Tools extends Dase_Handler
{
	public $resource_map = array(
		'/' => 'demo',
		'demo' => 'demo',
		'cd' => 'cache_deleter',
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
		$r->renderResponse($t->fetch('tools/demo.tpl'));
	}

	/** this handler method should be the target of a web hook */
	public function postToCacheDeleter($r)
	{
		$num = Dase_Cache_File::expunge();
		$r->renderResponse('cache deleted '.$num.' files removed');
	}
}
