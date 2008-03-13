<?php

class Dase_Cache_Exception extends Exception {
}


class Dase_Cache
{
	public function __construct($name='') {
		$type = Dase::getConf('cache');
		$class_name = 'Dase_Cache_'.ucfirst($type);
		if (class_exists($class_name)) {
			return new $class_name($name);
		} else {
			throw new Dase_Cache_Exception("Error: $class_name is not a valid class!");
		}
	}

	//must be overridden:
	 function get() {}
	 function set($data) {}
	 function setTimeToLive($exp) {}
	 function expire() {}
}

