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

	function check_auth($collection_ascii_id = null,$auth_level) {
		if (!$collection_ascii_id) {
			return false;
		}
		$cm = new Dase_DB_CollectionManager; 
		$cm->collection_ascii_id = $collection_ascii_id;
		$cm->dase_user_eid = $this->db_user->eid;
		$cm->findOne();
		if ($cm->auth_level) {
			if ('read' == $auth_level) {
				return true;
			} elseif ('write' == $auth_level && in_array($cm->auth_level,array('write','admin','manager','superuser'))) {
				return true;
			} elseif ('admin' == $auth_level && in_array($cm->auth_level,array('admin','manager','superuser'))) {
				return true;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}
}
