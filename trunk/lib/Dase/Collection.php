<?php

class Dase_Collection 

//this is a simple collection factory
//it allows collection type to be determined
//at runtime (by simply passing in desired type).  
//The Dase_CollectionInterface defines
//the methods implemented by any Dase_Collection

{
	private static $types_map = array(
		//store in xml??
		'xml' => array('class'=>'Dase_Xml_Collection'),
		'db' => array('class'=>'Dase_DB_Collection'),
		'remote' => array('class'=>'Dase_Remote_Collection'),
	);

	public static function get($ascii_id, $type) {
		$class =self::$types_map[$type]['class'];
		return call_user_func_array(array($class,"get"),array($ascii_id));
	}
}
