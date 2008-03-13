<?php

class Dase_Search 

//simple search factory

{
	private static $models_map = array(
		'xml' => array('class'=>'Dase_Xml_Search'),
		'db' => array('class'=>'Dase_DB_Search'),
		'remote' => array('class'=>'Dase_Remote_Search'),
	);

	public static function get() {
		$model = Dase::getConf('model');
		$class =self::$models_map[$model]['class'];
		return call_user_func(array($class,"get"));
	}
}
