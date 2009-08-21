<?php
Class Dase_User_Exception extends Exception {}

/* the idea here is to enable the dase framework to be used independently of
 * the dase application and so we need a generic user class to instantiates
 * whatever user class the app is supplying (i.e. allow different table name
 */

Class Dase_User 
{
	public $eid;
	public $is_serviceuser;

	function __construct() {}

	public static function get($db,$config)
	{
		$parts = explode('_',$config->getAppSettings('user_table')); 
		array_walk($parts, create_function('&$str', '$str = ucfirst($str);'));
		$class_name = 'Dase_DBO_'.join('',$parts);

		if (class_exists($class_name)) {
			return new $class_name($db);
		} else {
			throw new Dase_User_Exception("Error: $class_name is not a valid class!");
		}
	}

	//must be supplied by db user class:
	//public function retrieveByEid($eid) {}	

}


