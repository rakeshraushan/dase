<?php
ini_set('display_errors',1);
error_reporting(E_ALL);
ini_set('include_path','.:'.dirname(__FILE__).'/../lib');
require_once('simpletest/autorun.php');
require_once('simpletest/web_tester.php');
require_once('Dase/Http/Request.php');
require_once('Dase/Util.php');

class WebPageTest extends WebTestCase {
    
    function testCollectionPage() {
		$r = new Dase_Http_Request(dirname(__FILE__).'/..');
		$parts = explode('/',$r->app_root);
		array_pop($parts);
		$app_root = join('/',$parts);
	//	$this->dump($app_root);
        $this->get($app_root.'/collections');
        $this->assertTitle('DASe Login');
    }
}



