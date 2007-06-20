<?php

class Dase_Session {

	private static $instance;
	private $data = null;
	//cookie format info
	static $cookiename = 'DASE_SESSION';

	private function __construct() {}

	//singleton
	public static function instance() {
		if (empty( self::$instance )) {
			self::$instance = new Dase_Session();
		}
		return self::$instance;
	}

	public static function read() {
		$s = Dase_Session::instance();
		if (isset($_COOKIE[self::$cookiename])) {
			$s->data = unserialize(Dase_Encryption::decrypt($_COOKIE[self::$cookiename]));
			return $s->data;
		}
	}

	public static function write() {
		$s = Dase_Session::instance();
		setcookie(self::$cookiename,Dase_Encryption::encrypt(serialize($s->data)));
	}

	public static function destroy() {
		setcookie(self::$cookiename,'',-86400);
	}

	public static function set($key,$value) {
		$s = Dase_Session::instance();
		$s->data[$key] = $value;
	}

	public static function get($key) {
		$s = Dase_Session::instance();
		if (isset($s->data[$key])) {
			return $s->data[$key];
		}
	}
}


