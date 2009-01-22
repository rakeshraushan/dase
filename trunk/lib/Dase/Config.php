<?php

class Dase_Config {

	private static $conf;

	private static function _init()
	{
		if (is_null(self::$conf)) {
			$conf = array();
			include(DASE_CONFIG);
			self::$conf = $conf;
		}
	}

	public static function get($key)
	{
		self::_init();
		if (isset(self::$conf[$key])) {
			return self::$conf[$key];
		} else {
			return false;
		}
	}

	public static function getAll()
	{
		self::_init();
		return self::$conf;
	}

	public static function set($key,$value)
	{
		self::_init();
		self::$conf[$key] = $value;
	}

	public static function reload()
	{
		//allows module request to get module-defined config
		$conf = array();
		include(DASE_CONFIG);
		self::$conf = $conf;
	}
}
