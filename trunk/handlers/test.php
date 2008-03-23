<?php

include 'test-more.php';

class TestHandler
{
	public static function first($params)
	{
		$t = new Dase_Xslt;
		$t->stylesheet = XSLT_PATH.'test/index.xsl';
		$t->source = XSLT_PATH.'test/layout.xml';
		$test = new Dase_Test;
		$c = new Dase_Cache_File('test');
		$c->expire();
		$test->assertTrue(!file_exists(CACHE_DIR.md5('test')),'no cache file');
		$c = new Dase_Cache_File('test');
		$c->setData('hello');
		$test->assertTrue(file_exists(CACHE_DIR.md5('test')),'yes cache file');
		$t->addSourceNode($test->asSimpleXml());
		Dase::display($t->transform());
	}

	public static function search($params)
	{
		$t = new Dase_Xslt;
		$t->stylesheet = XSLT_PATH.'test/index.xsl';
		$t->source = XSLT_PATH.'test/layout.xml';
		$url = '';
		$qs = urlencode('q=dd -ddd kl or opl  ff -g&c=vrc&c=test&c=fine');

		$search = Dase_Search::get($url,$qs);
		$sql = $search->createSql();
		$placeholder_count = preg_match_all('/\?/',$sql,$matches);
		$param_count = count($search->bound_params);
		$test = new Dase_Test;
		$test->assertTrue($placeholder_count == $param_count,'placeholder count eq params count');
		$t->addSourceNode($test->asSimpleXml());
		Dase::display($t->transform());
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
