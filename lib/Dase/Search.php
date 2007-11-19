<?php

class Dase_Search 

{
	private static $models_map = array(
		'xml' => array('class'=>'Dase_Xml_Search'),
		'db' => array('class'=>'Dase_DB_Search'),
		'remote' => array('class'=>'Dase_Remote_Search'),
	);

	public static function get($params) {
		$model = Dase::getConf('model');
		$class =self::$models_map[$model]['class'];
		return call_user_func_array(array($class,"get"),array($params));
	}
}
