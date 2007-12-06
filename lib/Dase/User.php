<?php

class Dase_User 
{
	// this class should ONLY need to be instantiated
	// if there is an eid in the URL string...

	public $db_user = null;

	public function __construct() {
		$eid = Dase_User::getCurrent();
		if ($eid) {
			$db = Dase_DB::get();
			$sql = "
				SELECT * FROM dase_user
				WHERE lower(eid) = ?
				";	
			$sth = $db->prepare($sql);
			if ($sth->execute(array(strtolower($eid)))) {
				return new Dase_DB_DaseUser($sth->fetch());
			}
		} 
		//a "GET" to '/login/' should initiate the login process
		//Dase::reload('login');
		return false;
	}

	//factory method
	public static function get($eid) {
		$db = Dase_DB::get();
		$sql = "
			SELECT * FROM dase_user
			WHERE lower(eid) = ?
			";	
		$sth = $db->prepare($sql);
		if ($sth->execute(array(strtolower($eid)))) {
			return new Dase_DB_DaseUser($sth->fetch());
		}
	}


	public static function getCurrent() {
		if (isset($_SERVER['PHP_AUTH_USER']) && 
			isset($_SERVER['PHP_AUTH_PW']) && 
			$_SERVER['PHP_AUTH_PW'] == md5($_SERVER['PHP_AUTH_USER'] . Dase::getConf('token'))) {
				return $_SERVER['PHP_AUTH_USER'];
			} else {
				//should this be here???
				//(seems a bit dramatic)
				Dase::error(401);
				exit;
			}
	}

	public static function logoff() {
	}

	public function __get($prop) {
		if (defined('DEBUG')) {
			Dase::log('standard','__get from Dase_User prop: ' . $prop);
		}
		if ($this->db_user->$prop) {
			return $this->db_user->$prop;
		}
	}

	static function check_credentials($username,$password) {
		$auth_users = array();
		if (md5($username . Dase::getConf('token')) == $password) {
			$user = new Dase_DB_DaseUser();
			//need to account for case here!!!!!!!!!!!!!!!!!
			//needs to be case-insensitive
			$user->eid = $username;
			if ($user->findOne()) {
				return $user;
			}
		}
		return false;
	}

	function checkAuth($collection_ascii_id = null,$auth_level) {
		if (!$collection_ascii_id) {
			return false;
		}
		if ('read' == $auth_level) {
			// we can short circuit if curr coll is public
			// which is good, since this will be the case MOST
			// of the time
			if (Dase::instance()->collection) {
				if (Dase::instance()->collection->is_public) {
					return true;
				}
			}
		}
		$cm = new Dase_DB_CollectionManager; 
		$cm->collection_ascii_id = $collection_ascii_id;
		//need to account for case here!!!!!!!!!!!!!!!!!
		//needs to be case-insensitive
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
