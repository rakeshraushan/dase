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


class Dase_Registry_Exception extends Exception {
}

class Dase_Registry 
{
	private static $instance;
	private $members = array();

	private function __construct() {}

	//singleton
	private static function instance()
	{
		if (empty( self::$instance )) {
			self::$instance = new Dase_Registry();
		}
		return self::$instance;
	}

	public static function set($key,$value)
	{
		$reg = Dase_Registry::instance();
		if (!isset($reg->members[$key])) {
			$reg->members[$key] = $value;
		} else {
			throw new Dase_Registry_Exception("sorry, but $key is already set!");
		}
	}

	public static function get($key)
	{
		$reg = Dase_Registry::instance();
		if (isset($reg->members[$key])) {
			return $reg->members[$key];
		} else {
			return false;
		}
	}

	public static function dump() 
	{
		$reg = Dase_Registry::instance();
		return $reg->members;
	}

}
