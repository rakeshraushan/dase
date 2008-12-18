<?php

class Dase_Handler_Test extends Dase_Handler
{
	public $resource_map = array(
		'/' => 'demo',
		'demo' => 'demo',
		'cache' => 'cache',
		'search' => 'search',
		'fail' => 'fail',
	);

	protected function setup($r)
	{
	}

	public function getDemo($r)
	{
		$t = new Dase_Template($r);
		$r->renderResponse($t->fetch('test/demo.tpl'));
	}

	public function getCache($r)
	{
		$t = new Dase_Template($r);
		$test = new Dase_Test;
		$c = new Dase_Cache_File('test');
		$c->expire();
		$test->assertTrue(!file_exists(CACHE_DIR.md5('test')),'no cache file');
		$c = new Dase_Cache_File('test');
		$c->setData('hello');
		$test->assertTrue(file_exists(CACHE_DIR.md5('test')),'yes cache file');
		$t->assign('test',$test);
		$r->renderResponse($t->fetch('test/index.tpl'));
	}

	public function getSearch($r)
	{
		//todo: needs work!!!!!!!!!!!!!!!!!!
		$url = '';
		$qs = urlencode('q=dd -ddd kl or opl  ff -g&c=vrc&c=test&c=fine');

		$t = new Dase_Template($r);
		$search = Dase_Search::get($url,$qs);
		$sql = $search->createSql();
		$placeholder_count = preg_match_all('/\?/',$sql,$matches);
		$param_count = count($search->bound_params);
		$test = new Dase_Test;
		$test->assertTrue($placeholder_count == $param_count,'placeholder count eq params count');
		$t->assign('test',$test);
		$r->renderResponse($t->fetch('test/index.tpl'));
	}

	public function getFail($r)
	{
		$t = new Dase_Template($r);
		$test = new Dase_Test;
		$test->assertTrue(false,'fail');
		$t->assign('test',$test);
		$r->renderResponse($t->fetch('test/index.tpl'));
	}
}
