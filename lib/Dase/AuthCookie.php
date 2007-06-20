<?php

class AuthException extends Exception {}

class Dase_AuthCookie {

	//from Advanced PHP Programming p. 334

	private $created;
	private $userid;
	private $version;

	//cookie format info
	static $cookiename = 'DASE_USERAUTH';
	static $myversion = '1';
	// when to expire
	static $expiration = '600';
	//when to reissue
	static $resettime = '300';
	static $glue = '|';

	public function __construct($userid = false) {
		if ($userid) {
			$this->userid = $userid;
			return;
		} else {
			if (array_key_exists(self::$cookiename,$_COOKIE)) {
				$buffer = $this->_unpackage($_COOKIE[self::$cookiename]);
			} else {
				throw new AuthException("no cookie");
			}
		}
	}

	public function set() {
		$cookie = $this->_package();
		setcookie(self::$cookiename,$cookie,0,'/');
	}

	public function validate() {
		if(!$this->version || !$this->created || !$this->userid) {
			throw new AuthException('malformed cookie');
		}
		if ($this->version != self::$myversion) {
			throw new AuthException('version mismatch');
		}
		if (time() - $this->created  > self::$resettime) {
			$this->set();
		}
		return $this->userid;
	}

	public function logout() {
		setcookie(self::$cookiename,"",-86400,'/');
	}

	private function _package() {
		$parts = array(self::$myversion,time(),$this->userid);
		$cookie = implode(self::$glue,$parts);
		return Dase_Encryption::encrypt($cookie);
	}

	private function _unpackage($cookie) {
		$buffer = Dase_Encryption::decrypt($cookie);
		list($this->version,$this->created,$this->userid) = explode(self::$glue,$buffer);
		if ($this->version != self::$myversion ||
			!$this->created ||
			!$this->userid) {
				throw new AuthException();
			}
	}

	private function _reissue() {
		$this->created = time();
	}
}


