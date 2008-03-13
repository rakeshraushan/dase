<?php

class Dase_Registry_Exception extends Exception {
}

class Dase_Registry 
{
	private static $instance;
	private $members = array();

	private function __construct() {}

	//singleton
	private static function instance() {
		if (empty( self::$instance )) {
			self::$instance = new Dase_Registry();
		}
		return self::$instance;
	}

	public static function set($key,$value) {
		$reg = Dase_Registry::instance();
		if (!isset($reg->members[$key])) {
			$reg->members[$key] = $value;
		} else {
			throw new Dase_Registry_Exception("sorry, but $key is already set!");
		}
	}

	public static function get($key) {
		$reg = Dase_Registry::instance();
		if (isset($reg->members[$key])) {
			return $reg->members[$key];
		} else {
			return false;
		}
	}

}
