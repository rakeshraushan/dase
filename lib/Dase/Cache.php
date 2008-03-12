<?php

class Dase_Cache
{
	public function __construct($name='') {
		$type = Dase::getConf('cache');
		$class = 'Dase_Cache_'.ucfirst($type);
		//calls the subclass constructor
		return new $class($name);
	}

	//must be overridden:
	 function get() {}
	 function set() {}
	 function setTimeToLive() {}
	 function expire() {}
}

