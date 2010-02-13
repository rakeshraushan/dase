<?php

require_once('bootstrap.php');
require_once('simpletest/autorun.php');


class TestOfCache extends UnitTestCase {

	function setUp() {
		$c = new Dase_Config(BASE_PATH);
		$c->load('inc/config.php');
		$c->load('inc/local_config.php');
		$cache = Dase_Cache::get($c);
		$cache->expunge();
	}

	function tearDown() {
		$c = new Dase_Config(BASE_PATH);
		$c->load('inc/config.php');
		$c->load('inc/local_config.php');
		$cache = Dase_Cache::get($c);
		$cache->expunge();
	}

	/* NOTE: timestamp weirdness makes this fail
	 * from command line.  It passes on web
	 */
	function testDataIsCached() {
		$c = new Dase_Config(BASE_PATH);
		$c->load('inc/config.php');
		$c->load('inc/local_config.php');
		$cache = Dase_Cache::get($c);
		$cache->setData('my_cache_file','hello world');
		$data = $cache->getData('my_cache_file');
		$this->assertTrue('hello world' == $cache->getData('my_cache_file'));
	}

	/* NOTE: this is purposely slow
	 */
	/*
	function testDataIsExpired() {
		$c = new Dase_Config(BASE_PATH);
		$c->load('inc/config.php');
		$c->load('inc/local_config.php');
		$cache = Dase_Cache::get($c);
		$cache->setData('my_cache_file','hello world');
		$data = $cache->getData('my_cache_file');
		$this->dump('pausing for 2 seconds...');
		sleep(2);
		$this->assertFalse('hello world' == $cache->getData('my_cache_file',1));
	}
	 */
}



