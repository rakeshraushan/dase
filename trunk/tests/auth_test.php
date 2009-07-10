<?php
ini_set('display_errors',1);
error_reporting(E_ALL);
ini_set('include_path','.:'.dirname(__FILE__).'/../lib');
require_once('simpletest/autorun.php');
require_once('simpletest/web_tester.php');
require_once('Dase/Http/Request.php');
require_once('Dase/Util.php');

class AuthenticationTest extends WebTestCase {

	function setUp() {
	}

	function tearDown() {
	}

	function test401Header() {
		$r = new Dase_Http_Request('');
		$parts = explode('/',$r->app_root);
		array_pop($parts);
		$app_root = join('/',$parts);
		$this->get($app_root.'/user/pkeane/sets.atom');
		$this->assertAuthentication('Basic');
		$this->assertResponse(401);
		$this->assertRealm('DASe');
	}
}



