<?php

class Dase_Cache_Exception extends Exception {
}

class Dase_Cache
{
	private function __construct() {}

	public static function get($type,$ttl=10)
	{
		$class_name = 'Dase_Cache_'.ucfirst($type);
		if (class_exists($class_name)) {
			return new $class_name($ttl);
		} else {
			throw new Dase_Cache_Exception("Error: $class_name is not a valid class!");
		}
	}

	//must be overridden:
	public function expire($cache_id) {}
	public function getData($cache_id,$ttl) {}
	public function expunge() {}
	public function setData($cache_id,$data) {}
}


