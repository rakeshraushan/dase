<?php

class Dase_Auth
{
	public static function authorize($auth_type,$collection_ascii_id='',$eid='') {
		//auth type comes from routes.php
		//and there needs to be a corresponding Auth class
		$class_name = 'Dase_Auth_'.ucfirst($auth_type);
		$auth_class = new $class_name;
		$dase = Dase::instance();
		return $auth_class->authorize($dase,$collection_ascii_id,$eid);
	}
}

