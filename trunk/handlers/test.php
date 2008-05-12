<?php

class TestHandler
{
	public static function first($params)
	{
		$t = new Dase_Template;
		$test = new Dase_Test;
		$c = new Dase_Cache_File('test');
		$c->expire();
		$test->assertTrue(!file_exists(CACHE_DIR.md5('test')),'no cache file');
		$c = new Dase_Cache_File('test');
		$c->setData('hello');
		$test->assertTrue(file_exists(CACHE_DIR.md5('test')),'yes cache file');
		$t->assign('test',$test);
		Dase::display($t->fetch('test/index.tpl'));
	}

	public static function search($params)
	{
		$url = '';
		$qs = urlencode('q=dd -ddd kl or opl  ff -g&c=vrc&c=test&c=fine');

		$t = new Dase_Template;
		$search = Dase_Search::get($url,$qs);
		$sql = $search->createSql();
		$placeholder_count = preg_match_all('/\?/',$sql,$matches);
		$param_count = count($search->bound_params);
		$test = new Dase_Test;
		$test->assertTrue($placeholder_count == $param_count,'placeholder count eq params count');
		$t->assign('test',$test);
		Dase::display($t->fetch('test/index.tpl'));
	}

	public static function fail($params)
	{
		$t = new Dase_Template;
		$test = new Dase_Test;
		$test->assertTrue(false,'fail');
		$t->assign('test',$test);
		Dase::display($t->fetch('test/index.tpl'));
	}
}



/*
$url = 'collection/vrc/search';

if ($placeholder_count == $param_count) {
	print "OK on param count ($param_count)\n";
} else {
	print "ERROR: $placeholder_count placeholders and $param_count params\n";
}
 */
