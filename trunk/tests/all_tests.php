<?php

ini_set('include_path','.:'.dirname(__FILE__).'/../lib');

function __autoload($class_name) {
	@include __autoloadFilename($class_name);
}

function __autoloadFilename($class_name) {
	return str_replace('_','/',$class_name) . '.php';
}
require_once('simpletest/autorun.php');


class AllTests extends TestSuite {
	function __construct() {
		$this->TestSuite('All tests');
		$this->addFile(dirname(__FILE__).'/log_test.php');
		$this->addFile(dirname(__FILE__).'/cache_test.php');
		$this->addFile(dirname(__FILE__).'/db_test.php');
		$this->addFile(dirname(__FILE__).'/config_test.php');
		$this->addFile(dirname(__FILE__).'/compile_test.php');
		$this->addFile(dirname(__FILE__).'/auth_test.php');
		$this->addFile(dirname(__FILE__).'/web_page_test.php');
	}
}


