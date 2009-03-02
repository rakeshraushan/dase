<?php
ini_set('display_errors',1);
error_reporting(E_ALL);
ini_set('include_path','.:'.dirname(__FILE__).'/../lib');
require_once('simpletest/autorun.php');

class TestOfCompile extends UnitTestCase {

	function testAllSourceFilesCompile() {
		include('include_all.php');
		$this->assertTrue($success);
	}
}



