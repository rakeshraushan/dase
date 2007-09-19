<?php

require_once 'Dase/DB/Autogen/Value.php';

class Dase_DB_Value extends Dase_DB_Autogen_Value 
{
	public $attribute_name;
	public $attribute_ascii_id;

	//this might need renaming????????
	function getAttributeName() {
		$a = new Dase_DB_Attribute();
		$a->load($this->attribute_id);	
		$this->attribute_name = $a->attribute_name;
		$this->attribute_ascii_id = $a->ascii_id;
	}
}
