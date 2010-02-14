<?php

class Dase_Handler_Test extends Dase_Handler
{

	public $resource_map = array( 
		'/' => 'index',
		'auth' => 'require_auth',
		'cache' => 'cache',
	);

	protected function setup($r)
	{
	}	

	public function getIndex($r) 
	{
		$tpl = new Dase_Template($r);
		$r->renderResponse($tpl->fetch('test/index.tpl'));
	}

	public function getCache($r) 
	{
		$tpl = new Dase_Template($r);
		$cache = Dase_Cache::get($this->config);
		$cache->setData('my_cache_file','hello world cached');
		$data = $cache->getData('my_cache_file');
		$r->renderResponse($data);
	}


}

