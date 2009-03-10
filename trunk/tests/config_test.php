<?php
ini_set('display_errors',1);
error_reporting(E_ALL);
ini_set('include_path','.:'.dirname(__FILE__).'/../lib');
require_once('simpletest/autorun.php');
require_once('Dase/Config.php');


class TestOfConfig extends UnitTestCase {

	function testConfigDirIsCreated() {
		$c = new Dase_Config(dirname(__FILE__).'/..');
		$c->load('inc/config.php');
		$c->load('inc/local_config.php');
		$c->load('tests/test_config.php');
		$test = $c->getAppSettings('test');
		$this->assertTrue('dase' == $test);
	}
}



