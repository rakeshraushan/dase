<?php
ini_set('display_errors',1);
error_reporting(E_ALL);
ini_set('include_path','.:'.dirname(__FILE__).'/../lib');
require_once('simpletest/autorun.php');
require_once('simpletest/web_tester.php');

class AuthenticationTest extends WebTestCase {
	function test401Header() {
		$this->get('http://quickdraw.laits.utexas.edu/dase1/user/pkeane/sets.atom');
		$this->assertAuthentication('Basic');
		$this->assertResponse(401);
		$this->assertRealm('DASe');
	}
}


