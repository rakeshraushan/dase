<?php

require_once('simpletest/autorun.php');
require_once('Dase/DB.php');

define('DASE_PATH', dirname(__FILE__).'/..');
define('DASE_LOG', DASE_PATH . '/log/dase.log');


class TestOfLogging extends UnitTestCase {

	function testLogFileExists() {
		Dase_Log::get()->start();
		$this->assertTrue(file_exists(Dase_Log::get()->getFilename()));
	}

	function testLogFileWriteable() {
		Dase_Log::get()->start();
		$this->assertTrue(is_writeable(Dase_Log::get()->getFilename()));
	}
}



