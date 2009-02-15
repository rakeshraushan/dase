<?php

class Dase_Cache_Exception extends Exception {
}


class Dase_Cache
{
	public function __construct($type,$cache_dir,$ttl=10,$server_ip='localhost')
	{
		$class_name = 'Dase_Cache_'.ucfirst($type);
		if (class_exists($class_name)) {
			return new $class_name($cache_dir,$ttl,$server_ip);
		} else {
			throw new Dase_Cache_Exception("Error: $class_name is not a valid class!");
		}
	}

	//must be overridden:
	public function expire() {}
	public function getData() {}
	public static function expunge() {}
	public function setData($data) {}
}


