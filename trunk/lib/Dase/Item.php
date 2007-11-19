<?php

class Dase_Item 

{
	private static $models_map = array(
		'xml' => array('class'=>'Dase_Xml_Item'),
		'db' => array('class'=>'Dase_DB_Item'),
		'remote' => array('class'=>'Dase_Remote_Item'),
	);

	public static function get($collection_ascii_id,$serial_number) {
		$model = Dase::getConf('model');
		$class =self::$models_map[$model]['class'];
		return call_user_func_array(array($class,"get"),array($collection_ascii_id,$serial_number));
	}
}
