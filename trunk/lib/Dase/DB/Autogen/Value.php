<?php

require_once 'Dase/DB/Object.php';

class Dase_DB_Autogen_Value extends Dase_DB_Object 
{
	function __construct($assoc = false) {
		parent::__construct( 'value',  array('attribute_id','item_id','value_text'));
		if ($assoc) {
			foreach ( $assoc as $key => $value) {
				$this->$key = $value;
			}
		}
	}
}