<?php

require_once 'Dase/DB/Object.php';

class Dase_DB_Autogen_AttributeItemType extends Dase_DB_Object 
{
	function __construct($assoc = false) {
		parent::__construct( 'attribute_item_type',  array('attribute_id','is_compiled_as_attribute','item_type_id'));
		if ($assoc) {
			foreach ( $assoc as $key => $value) {
				$this->$key = $value;
			}
		}
	}
}