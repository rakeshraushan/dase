<?php

require_once 'Dase/DB/Object.php';

class Dase_DB_Autogen_AttributeCategory extends Dase_DB_Object 
{
	function __construct($assoc = false) {
		parent::__construct( 'attribute_category',  array('attribute_id','category_id','sort_order'));
		if ($assoc) {
			foreach ( $assoc as $key => $value) {
				$this->$key = $value;
			}
		}
	}
}