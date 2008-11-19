<?php

class Dase_Cookie {

	//this class deals with the eid cookie and the 
	//encrypted eid cookie AND provides minimal
	//generic functionality

	static $user_cookiename = 'DASE_USER';
	static $auth_cookiename = 'DASE_AUTH';
	static $cookiemap = array(
		'max' => 'DASE_MAX_ITEMS',
		'display' => 'DASE_DISPLAY_FORMAT',
	);
	static $display_cookiename = 'DASE_DISPLAY_FORMAT';
	private $token;


	private static function getPrefix() 
	{
		//NOTE that the cookie name will be unique per dase instance 
		//(note: HAD been doing it by date, but that's no good when browser & server
		//dates disagree)
		$prefix = str_replace('http://','',APP_ROOT);
		$prefix = str_replace('.','_',$prefix);
		return str_replace('/','_',$prefix) . '_';
	}

	public static function setEid($eid) 
	{
		$pre = Dase_Cookie::getPrefix();
		$key = md5(Dase_Config::get('token').$eid);
		setcookie($pre . self::$user_cookiename,$eid,0,'/');
		setcookie($pre . self::$auth_cookiename,$key,0,'/');
	}

	public static function set($type,$data) 
	{
		$pre = Dase_Cookie::getPrefix();
		if (isset(self::$cookiemap[$type])) {
			$cookiename = $pre . self::$cookiemap[$type];
			setcookie($cookiename,$data,0,'/');
		}
	}

	public static function get($type) 
	{
		$pre = Dase_Cookie::getPrefix();
		if (isset(self::$cookiemap[$type])) {
			$cookiename = $pre . self::$cookiemap[$type];
			if (isset($_COOKIE[$cookiename])) {
				return $_COOKIE[$cookiename];
			}
		}
	}

	/** simply checks the cookie */
	public static function getEid() 
	{
		$pre = Dase_Cookie::getPrefix();
		$token = Dase_Config::get('token');
		$key = '';
		$eid = '';
		if (isset($_COOKIE[$pre . self::$user_cookiename])) {
			$eid = $_COOKIE[$pre . self::$user_cookiename];
		}
		if (isset($_COOKIE[$pre . self::$auth_cookiename])) {
			$key = $_COOKIE[$pre . self::$auth_cookiename];
		}
		if ($key && $eid && $key == md5($token.$eid)) {
			return $eid;
		}
		return false;
	}

	public static function clear() 
	{
		$pre = Dase_Cookie::getPrefix();
		setcookie($pre . self::$user_cookiename,"",-86400,'/');
		setcookie($pre . self::$auth_cookiename,"",-86400,'/');
	}
}


