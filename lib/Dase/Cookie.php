<?php

class Dase_Cookie {

	static $user_cookiename = 'DASE_USER';
	static $auth_cookiename = 'DASE_AUTH';
	private $token;


	private static function getPrefix() {
		//NOTE that the cookie name will be unique per dase instance 
		//(note: HAD been doing it by date, bu that's no good when browser & server
		//dates disagree)
		$prefix = str_replace('http://','',APP_ROOT);
		$prefix = str_replace('.','_',$prefix);
		return str_replace('/','_',$prefix) . '_';
	}

	public static function set($eid) {
		$pre = Dase_Cookie::getPrefix();
		$key = md5(Dase::getConf('token').$eid);
		setcookie($pre . self::$user_cookiename,$eid,0,'/');
		setcookie($pre . self::$auth_cookiename,$key,0,'/');
	}

	public static function validate() {
		$pre = Dase_Cookie::getPrefix();
		$token = Dase::getConf('token');
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

	public static function clear() {
		$pre = Dase_Cookie::getPrefix();
		setcookie($pre . self::$user_cookiename,"",-86400,'/');
		setcookie($pre . self::$auth_cookiename,"",-86400,'/');
	}
}


