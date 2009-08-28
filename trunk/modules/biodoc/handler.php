<?php

class Dase_ModuleHandler_Biodoc extends Dase_Handler 
{
	public $resource_map = array(
		'/' => 'index',
		'index' => 'index',
		'topics' => 'topics',
		'about' => 'about',
		'contribute' => 'contribute',
		'plugin' => 'plugin',
		'contact' => 'contact',
		'search' => 'search',
	);

	public function setup($r)
	{
	}

	public function getIndex($r) 
	{
		$t = new Dase_Template($r,true);
		//$t->assign('feed',Dase_Atom_Feed::retrieve($r->app_root."/search.atom?q=host:wheat"));
		$r->renderResponse($t->fetch('index.tpl'));
	}

	public function getSearch($r) 
	{
		$t = new Dase_Template($r,true);
		$unit = urlencode($r->get('unit'));
		$topic = urlencode($r->get('topic'));
		$feed = Dase_Atom_Feed::retrieve($r->app_root.'/search.atom?q=c:biodoc+unit:"'.$unit.'"+topic:"'.$topic.'"&max=9999');
		$t->assign('feed',$feed);
		$r->renderResponse($feed->asXml());
	}

	public function getTopicsJson($r) 
	{
		$unit = $r->get('unit');
		$r->renderResponse($unit);
	}

	public function getAbout($r) 
	{
		$t = new Dase_Template($r,true);
		$r->renderResponse($t->fetch('about.tpl'));
	}

	public function getContribute($r) 
	{
		$t = new Dase_Template($r,true);
		$r->renderResponse($t->fetch('contribute.tpl'));
	}

	public function getPlugin($r) 
	{
		$t = new Dase_Template($r,true);
		$r->renderResponse($t->fetch('plugin.tpl'));
	}

	public function getContact($r) 
	{
		$t = new Dase_Template($r,true);
		$r->renderResponse($t->fetch('contact.tpl'));
	}
}
