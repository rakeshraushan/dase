<?php
ini_set('include_path','.:'.dirname(__FILE__).'/../lib');

require_once('simpletest/autorun.php');
require_once('Dase/DB.php');
require_once('Dase/DBO/Collection.php');
require_once('Dase/Log.php');


class TestOfDatabase extends UnitTestCase {

	function testDatabaseCanConnect() {
		$db_settings = array(
			'type' => 'pgsql',
			'host' => 'postgres.laits.utexas.edu',
			'name' => 'dase_dev',
			'user' => 'dase_dev',
			'pass' => 'dase_dev_user88',
			'table_prefix' => '',
		);
		$db = new Dase_DB($db_settings,new Dase_Log);
		$this->assertTrue($dbh = $db->getDbh());
	}
	function testDatabaseSelect() {
		$db_settings = array(
			'type' => 'pgsql',
			'host' => 'postgres.laits.utexas.edu',
			'name' => 'dase_dev',
			'user' => 'dase_dev',
			'pass' => 'dase_dev_user88',
			'table_prefix' => '',
		);
		$db = new Dase_DB($db_settings,new Dase_Log);
		$c = Dase_DBO_Collection::get($db,'test');
		$this->assertTrue('Test Collection' == $c->collection_name);
	}
}



