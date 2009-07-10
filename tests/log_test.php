<?php
ini_set('display_errors',1);
error_reporting(E_ALL);
ini_set('include_path','.:'.dirname(__FILE__).'/../lib');
require_once('simpletest/autorun.php');
require_once('Dase/Config.php');
require_once('Dase/Log.php');


class TestOfLogging extends UnitTestCase {

	function setUp() {
	}

	function tearDown() {
	}

	function testLogWritesToLogFile() {
		Dase_Log::info(LOG_FILE,'test');
		$val = substr(Dase_Log::readLastLine(LOG_FILE),-4);
		$this->assertTrue('test' === $val);
	}
}



