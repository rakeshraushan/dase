<?php
ini_set('display_errors',1);
error_reporting(E_ALL);
ini_set('include_path','.:'.dirname(__FILE__).'/../lib');
require_once('simpletest/autorun.php');
require_once('Dase/Config.php');


class TestOfConfig extends UnitTestCase {

	function testConfigDirIsCreated() {
		$c = new Dase_Config(dirname(__FILE__));
		$c->load('inc/config.php');
		$c->load('inc/local_config.php');
		$cache_dir = $c->getCacheDir();
		$this->assertTrue($cache_dir = dirname(__FILE__).'/files/cache');
	}
}



