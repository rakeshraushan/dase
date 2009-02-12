<?php

class Dase_Cache_Exception extends Exception {
}


class Dase_Cache
{
	private function __construct() {}

	public function get($filename) 
	{
		$type = Dase_Config::get('cache');
		$class_name = 'Dase_Cache_'.ucfirst($type);
		if (class_exists($class_name)) {
			return new $class_name($filename);
		} else {
			throw new Dase_Cache_Exception("Error: $class_name is not a valid class!");
		}
	}

	//must be overridden:
	function expire() {}
	function getData() {}
	public static function expunge() {}
	function setData($data) {}
}


