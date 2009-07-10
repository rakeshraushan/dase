<?php

class Dase_DocStore_Exception extends Exception {}

class Dase_DocStore
{
	protected $db;

	private function __construct() {}

	public static function get($db,$config)
	{

		$class_name = 'Dase_DocStore_'.ucfirst($config->getDocStore('type'));
		if (class_exists($class_name)) {
			return new $class_name($db,$config);
		} else {
			throw new Dase_DocStore_Exception("Error: $class_name is not a valid class!");
		}
	}

	//must be overridden:
	public function storeItem($item,$freshness=0) {}
	public function getItem($item_unique,$app_root,$as_feed=false){}
	public function getTimestamp($item_unique){}

}


