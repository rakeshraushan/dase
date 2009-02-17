<?php

ini_set('include_path','.:'.dirname(__FILE__).'/../lib');

require_once('simpletest/autorun.php');


class AllTests extends TestSuite {
	function __construct() {
		$this->TestSuite('All tests');
		//$this->addFile(dirname(__FILE__).'/log_test.php');
		$this->addFile(dirname(__FILE__).'/cache_test.php');
	}
}


