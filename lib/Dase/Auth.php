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

class Dase_Auth_Exception extends Exception {
}

//simple factory class that offers one method: "authorize"
//a call to the static function Dase_Auth::authorize($type,$coll_ascii,$eid)
//will instantiate proper subclass and run authorize method
//for new authorization method, create a new subclass

class Dase_Auth
{

	public static function authorize($auth_type,$params) 
	{
		//auth type comes from routes.php
		//and there needs to be a corresponding Auth class
		$class_name = 'Dase_Auth_'.ucfirst($auth_type);
		if (class_exists($class_name)) {
			$auth_class = new $class_name;
			return $auth_class->authorize($params);
		} else {
			//throw new Dase_Auth_Exception("Error: $class_name is not a valid class!");
			Dase::log('error',"$class_name is not a valid Dase_Auth_ class!");
			Dase::error(500);
		}
	}
}

