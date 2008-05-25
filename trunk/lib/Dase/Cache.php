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

class Dase_Cache_Exception extends Exception {
}


class Dase_Cache
{
	private function __construct() {}

	public function get($http_request_or_filename) 
	{
		$type = Dase::getConfig('cache');
		$class_name = 'Dase_Cache_'.ucfirst($type);
		if (class_exists($class_name)) {
			return new $class_name($http_request_or_filename);
		} else {
			throw new Dase_Cache_Exception("Error: $class_name is not a valid class!");
		}
	}

	//must be overridden:
	function display($ttl) {}
	function expire() {}
	function getData() {}
	function isFresh($ttl) {}
	function sendHeaders() {}
	function setData($data) {}

}


