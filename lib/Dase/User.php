<?php

class Dase_User 
{
	public $db_user = null;

	public function __construct() {
		try {
			$cookie = new Dase_AuthCookie;
			$user_id = $cookie->validate();
			if ($user_id) {
				$db_user = new Dase_DB_DaseUser;
				$db_user->eid = $user_id;
				$db_user->findOne();
				$this->db_user = $db_user;
			}
		}
		catch (AuthException $e) {
			Dase::reload('login_form');
		}
	}

	public function __get($prop) {
		if (DEBUG) {
			Dase::log('standard','__get from Dase_User prop: ' . $prop);
		}
		if ($this->db_user->$prop) {
			return $this->db_user->$prop;
		}
	}

	static function check_credentials($username,$password) {
		$auth_users = array();
		if ('pass' == $password) {
			$user = new Dase_DB_DaseUser();
			$user->eid = $username;
			if ($user->findOne()) {
				return $user;
			}
		}
		return false;
	}
}
