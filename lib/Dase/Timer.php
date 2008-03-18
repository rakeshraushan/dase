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

class Dase_Timer {

	private $_start;
	private static $instance;

	private function __construct()
	{
		$this->_start = self::microtime_float();
	}

	public static function start()
	{
		if (empty (self::$instance)) {
			self::$instance = new Dase_Timer();
		} else {
			throw new Exception( 'timer was already started' ); 
		}
		return self::$instance;
	}

	static function microtime_float()
	{
		list($usec, $sec) = explode(" ", microtime());
		return ((float)$usec + (float)$sec);
	} 

	public static function getElapsed()
	{
		if (empty (self::$instance)) {
			throw new Exception( 'timer was not started' ); 
		}
		return round(self::microtime_float() - self::$instance->_start,4);
	}
}

