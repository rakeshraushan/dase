<?php
ini_set('include_path','.:'.dirname(__FILE__).'/../lib');

require_once('simpletest/autorun.php');
require_once('Dase/Config.php');
require_once('Dase/DB.php');
require_once('Dase/DBO/Collection.php');
require_once('Dase/Log.php');


class TestOfDatabase extends UnitTestCase {

	function testDatabaseCanConnect() {
		$c = new Dase_Config(dirname(__FILE__).'/..');
		$c->load('inc/config.php');
		$c->load('inc/local_config.php');
		$log = new Dase_Log($c->getLogDir(),'dase.log',Dase_Log::DEBUG);
		$db = new Dase_DB($c->get('db'),$log);
		$this->assertTrue($dbh = $db->getDbh());
	}
	function testDatabaseSelect() {
		$c = new Dase_Config(dirname(__FILE__).'/..');
		$c->load('inc/config.php');
		$c->load('inc/local_config.php');
		$log = new Dase_Log($c->getLogDir(),'dase.log',Dase_Log::DEBUG);
		$db = new Dase_DB($c->get('db'),$log);
		$c = Dase_DBO_Collection::get($db,'test');
		$this->assertTrue('Test Collection' == $c->collection_name);
	}
}



