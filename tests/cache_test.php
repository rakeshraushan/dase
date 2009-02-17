<?php
ini_set('display_errors',1);
error_reporting(E_ALL);
ini_set('include_path','.:'.dirname(__FILE__).'/../lib');
require_once('simpletest/autorun.php');
require_once('Dase/Cache.php');
require_once('Dase/Cache/File.php');


class TestOfCache extends UnitTestCase {

	function testCacheDirIsCreated() {
		$cache_dir = dirname(__FILE__).'/temp/cache_dir';
		@unlink($cache_dir.'/files');
		$cache = Dase_Cache::get('file',$cache_dir);
		$this->assertTrue(file_exists($cache->getCacheDir()));
	}
}



