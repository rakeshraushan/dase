<?php

class Dase_Auth_Exception extends Exception {
}

//simple factory class that offers one method: "authorize"
//a call to the static function Dase_Auth::authorize($type,$coll_ascii,$eid)
//will instantiate proper subclass and run authorize method
//for new authorization method, create a new subclass

class Dase_Auth
{

	public static function authorize($auth_type,$collection_ascii_id='',$eid='') {
		//auth type comes from routes.php
		//and there needs to be a corresponding Auth class
		$class_name = 'Dase_Auth_'.ucfirst($auth_type);
		if (class_exists($class_name)) {
			$auth_class = new $class_name;
			return $auth_class->authorize($collection_ascii_id,$eid);
		} else {
			throw new Dase_Auth_Exception("Error: $class_name is not a valid class!");
		}
	}
}

