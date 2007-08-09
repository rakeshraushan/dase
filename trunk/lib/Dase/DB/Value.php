<?php

require_once 'Dase/DB/Autogen/Value.php';

class Dase_DB_Value extends Dase_DB_Autogen_Value 
{
	public $attribute_name;

	function getAttributeName() {
		$a = new Dase_DB_Attribute();
		$a->load($this->attribute_id);	
		$this->attribute_name = $a->attribute_name;
	}
}
