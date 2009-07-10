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
		$c = new Dase_Config(dirname(__FILE__).'/..');
		$c->load('inc/config.php');
		$c->load('inc/local_config.php');
		$log = new Dase_Log($c->getLogDir(),'test.log',Dase_Log::DEBUG);
		$log->delete();
	}

	function testLogCreatesNewFileOnFirstMessage() {
		$c = new Dase_Config(dirname(__FILE__).'/..');
		$c->load('inc/config.php');
		$c->load('inc/local_config.php');
		$log = new Dase_Log($c->getLogDir(),'test.log',Dase_Log::DEBUG);
		$log->delete();
		$this->assertFalse(file_exists($log->getFilename()));
		$log->info('should write this to file');
		$this->assertTrue(file_exists($log->getFilename()));
	}

	function testLogWritesToLogFile() {
		$c = new Dase_Config(dirname(__FILE__).'/..');
		$c->load('inc/config.php');
		$c->load('inc/local_config.php');
		$log = new Dase_Log($c->getLogDir(),'test.log',Dase_Log::DEBUG);
		$log->info('test');
		$val = substr(array_pop($log->getAsArray()),-4);
		$this->assertTrue('test' == $val);
	}
}



