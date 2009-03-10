<?php
ini_set('display_errors',1);
error_reporting(E_ALL);
ini_set('include_path','.:'.dirname(__FILE__).'/../lib');
require_once('simpletest/autorun.php');
require_once('Dase/Cache.php');
require_once('Dase/Cache/File.php');
require_once('Dase/Config.php');


class TestOfCache extends UnitTestCase {

	function setUp() {
		$c = new Dase_Config(dirname(__FILE__).'/..');
		$c->load('inc/config.php');
		$c->load('inc/local_config.php');
		$cache = Dase_Cache::get('file',$c->getCacheDir());
		$cache->expunge();
	}

	function tearDown() {
		$c = new Dase_Config(dirname(__FILE__).'/..');
		$c->load('inc/config.php');
		$c->load('inc/local_config.php');
		$cache = Dase_Cache::get('file',$c->getCacheDir());
		$cache->expunge();
	}

	/* NOTE: timestamp weirdness makes this fail
	 * from command line.  It passes on web
	 */
	function testDataIsCached() {
		$c = new Dase_Config(dirname(__FILE__).'/..');
		$c->load('inc/config.php');
		$c->load('inc/local_config.php');
		$this->dump($c->getCacheDir());
		$cache = Dase_Cache::get('file',$c->getCacheDir());
		$cache->setData('my_cache_file','hello world');
		$data = $cache->getData('my_cache_file');
		$this->dump($cache->getData('my_cache_file'));
		$this->assertTrue('hello world' == $cache->getData('my_cache_file'));
	}

	/* NOTE: this is purposely slow
	 */
	function testDataIsExpired() {
		$c = new Dase_Config(dirname(__FILE__).'/..');
		$c->load('inc/config.php');
		$c->load('inc/local_config.php');
		$cache = Dase_Cache::get('file',$c->getCacheDir());
		$cache->setData('my_cache_file','hello world');
		$data = $cache->getData('my_cache_file');
		$this->dump(date(DATE_ATOM));
		$this->dump('pausing for 2 seconds...');
		sleep(2);
		$this->assertFalse('hello world' == $cache->getData('my_cache_file',1));
	}
}



