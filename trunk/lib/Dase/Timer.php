<?php
class Dase_Timer {

	private $_start;
	private static $instance;

	private function __construct()
	{
		$this->_start = self::microtime_float();
	}

	public static function start()
	{
		if (empty (self::$instance)) {
			self::$instance = new Dase_Timer();
		} else {
			throw new Exception( 'timer was already started' ); 
		}
		return self::$instance;
	}

	static function microtime_float()
	{
		list($usec, $sec) = explode(" ", microtime());
		return ((float)$usec + (float)$sec);
	} 

	public static function getElapsed()
	{
		if (empty (self::$instance)) {
			throw new Exception( 'timer was not started' ); 
		}
		return round(self::microtime_float() - self::$instance->_start,4);
	}
}

