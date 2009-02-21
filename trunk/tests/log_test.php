<?php
ini_set('display_errors',1);
error_reporting(E_ALL);
ini_set('include_path','.:'.dirname(__FILE__).'/../lib');
require_once('simpletest/autorun.php');
require_once('Dase/Log.php');


class TestOfLogging extends UnitTestCase {

	function testLogCreatesNewFileOnFirstMessage() {
		$logfile = dirname(__FILE__).'/temp/dase.log';
		@unlink($logfile);
		$log = new Dase_Log(dirname(__FILE__).'/temp/','dase.log',Dase_Log::DEBUG);
		$this->assertFalse(file_exists($logfile));
		$log->info('should write this to file');
		$this->assertTrue(file_exists($logfile));
	}

	function testLogWritesToLogFile() {
		$logfile = dirname(__FILE__).'/temp/dase.log';
		@unlink($logfile);
		$log = new Dase_Log(dirname(__FILE__).'/temp/','dase.log',Dase_Log::DEBUG);
		$log->info('first');
		$log->truncate();
		$log->info('test');
		$val = substr(array_pop($log->getAsArray()),-4);
		$this->assertTrue('test' == $val);
	}
}



