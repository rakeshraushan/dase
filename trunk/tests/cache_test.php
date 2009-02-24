<?php
ini_set('display_errors',1);
error_reporting(E_ALL);
ini_set('include_path','.:'.dirname(__FILE__).'/../lib');
require_once('simpletest/autorun.php');
require_once('Dase/Cache.php');
require_once('Dase/Cache/File.php');


class TestOfCache extends UnitTestCase {

	function setUp() {
		$cache_dir = dirname(__FILE__).'/files/cache';
		$cache = Dase_Cache::get('file',$cache_dir);
		$cache->expunge();
		rmdir($cache_dir);
	}

	function tearDown() {
		$cache_dir = dirname(__FILE__).'/files/cache';
		$cache = Dase_Cache::get('file',$cache_dir);
		$cache->expunge();
		rmdir($cache_dir);
	}


	function testCacheDirIsCreated() {
		$cache_dir = dirname(__FILE__).'/files/cache';
		$this->assertFalse(file_exists($cache_dir));
		$cache = Dase_Cache::get('file',$cache_dir);
		$this->assertTrue(file_exists($cache_dir));
	}

	/* NOTE: timestamp weirdness makes this fail
	 * from command line.  It passes on web
	 */
	function testDataIsCached() {
		$cache_dir = dirname(__FILE__).'/files/cache';
		$cache = Dase_Cache::get('file',$cache_dir);
		$cache->setData('my_cache_file','hello world');
		$data = $cache->getData('my_cache_file');
		//$this->dump($cache->getData('my_cache_file'));
		$this->assertTrue('hello world' == $cache->getData('my_cache_file'));
	}

	/* NOTE: this is purposely slow
	 */
	function testDataIsExpired() {
		$cache_dir = dirname(__FILE__).'/files/cache';
		$cache = Dase_Cache::get('file',$cache_dir);
		$cache->setData('my_cache_file','hello world');
		$data = $cache->getData('my_cache_file');
		$this->dump(date(DATE_ATOM));
		$this->dump('pausing for 2 seconds...');
		sleep(2);
		$this->assertFalse('hello world' == $cache->getData('my_cache_file',1));
	}
}



