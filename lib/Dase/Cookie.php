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

class Dase_Cookie {

	//this class just deals with the eid cookie and the 
	//encrypted eid cookie

	static $user_cookiename = 'DASE_USER';
	static $auth_cookiename = 'DASE_AUTH';
	private $token;


	private static function getPrefix() 
	{
		//NOTE that the cookie name will be unique per dase instance 
		//(note: HAD been doing it by date, bu that's no good when browser & server
		//dates disagree)
		$prefix = str_replace('http://','',APP_ROOT);
		$prefix = str_replace('.','_',$prefix);
		return str_replace('/','_',$prefix) . '_';
	}

	public static function set($eid) 
	{
		$pre = Dase_Cookie::getPrefix();
		$key = md5(Dase_Config::get('token').$eid);
		setcookie($pre . self::$user_cookiename,$eid,0,'/');
		setcookie($pre . self::$auth_cookiename,$key,0,'/');
	}

	public static function validate() 
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


