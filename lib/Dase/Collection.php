<?php

class Dase_Collection 

//this is a simple collection factory
//it allows collection type to be determined
//at runtime in inc/config.php.  

{
	private static $models_map = array(
		'xml' => array('class'=>'Dase_Xml_Collection'),
		'db' => array('class'=>'Dase_DB_Collection'),
	);

	public static function get($ascii_id) {
		$model = Dase::getConf('model');
		$class =self::$models_map[$model]['class'];
		return call_user_func_array(array($class,"get"),array($ascii_id));
	}
}
