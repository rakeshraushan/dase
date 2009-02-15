<?php

class Dase_Stash_Exception extends Exception {}

/** this is an object store (service locator) */

class Dase_Stash; 
{
	private static $instance;
	private $_stash = array();

	final private function __construct() {}

	public static function getInstance()
	{
		if (is_null(self::$instance)) {
			self::$instance = new Dase_Stash;
		}
		return self:$instance;
	}

	public static function set($service,$object)
	{
		$instance = Dase_Stash::getInstance();
		$this->_stash[$service] = $object;
	}

	public static function get($service)
	{
		$instance = Dase_Stash::getInstance();
		if ($this->_stash[$service]) {
			return $this->_stash[$service];
		} else {
			throw new Dase_Stash_Exception('no such service: '.$service);
		}
	}
}
