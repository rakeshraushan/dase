<?php
/*
 * Copyright 2008 The University of Texas at Austin
 *
 * This file is part of DASe.
 * 
 * DASe is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * DASe is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with DASe.  If not, see <http://www.gnu.org/licenses/>.
 */ 


class Dase_User 
{
	// this class should ONLY need to be instantiated
	// if there is an eid in the URL string...

	public $db_user = null;

	public function __construct()
	{
		//check for current user (look to the cookie)
		$eid = Dase_User::getCurrent();
		if ($eid) {
			$this->db_user = Dase_User::get($eid);
		} 
	}

	//factory method
	public static function get($eid)
	{
		//allows you to pass 'params' in
		if (is_array($eid) && isset($eid['eid'])) {
			$eid = $eid['eid'];
		}

		//caches instance in registry
		$user = Dase_Registry::get($eid.'_user');
		if (!$user) {
			$db = Dase_DB::get();
			$sql = "
				SELECT * FROM dase_user
				WHERE lower(eid) = ?
				";	
			$sth = $db->prepare($sql);
			if ($sth->execute(array(strtolower($eid)))) {
				$user = new Dase_DBO_DaseUser($sth->fetch());
			}
			Dase_Registry::set($eid.'_user',$user);
		}
		return $user;
	}

	public static function getCurrent()
	{
		//attempt to validate cookie
		//since token changes every day, it'll be
		//invalidated overnight
		$eid = Dase_Cookie::validate();
		if ($eid) {
			return $eid;
		} else {
			return false;
		}
	}

	public static function logoff()
	{
		Dase_Cookie::clear();
	}

	public function __get($prop)
	{
		//this method gets invoked when $user->eid is referenced
		if (defined('DEBUG')) {
			Dase_Log::put('standard','__get from Dase_User prop: ' . $prop);
		}
		//note that I cannot do an isset here since we are using __get
		//magic method on db_user as well!!
		if (isset($this->db_user)) {
			if ($this->db_user->$prop) {
				return $this->db_user->$prop;
			}
		}
	}

	static function check_credentials($username,$password)
	{
		$auth_users = array();
		if (md5($username . Dase_Config::get('token')) == $password) {
			$user = new Dase_DBO_DaseUser();
			//need to account for case here!!!!!!!!!!!!!!!!!
			//needs to be case-insensitive
			$user->eid = $username;
			if ($user->findOne()) {
				return $user;
			}
		}
		return false;
	}

	function checkAuth($collection_ascii_id = null,$auth_level)
	{
		if (!$collection_ascii_id || !isset($this->db_user)) {
			return false;
		}
		if ('read' == $auth_level) {
			// we can short circuit if curr coll is public
			// which is good, since this will be the case MOST
			// of the time
			if (Dase_DBO_Collection::get($collection_ascii_id)->is_public) {
				return true;
			}
		}
		$cm = new Dase_DBO_CollectionManager; 
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
