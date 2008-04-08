<?php

require_once 'Dase/DBO/Autogen/Value.php';

class Dase_DBO_Value extends Dase_DBO_Autogen_Value 
{
	public $attribute = null;
	public $attribute_name;
	public $attribute_ascii_id;

	//this might need renaming????????
	function getAttribute()
	{
		$a = new Dase_DBO_Attribute();
		$a->load($this->attribute_id);	
		$this->attribute_name = $a->attribute_name;
		$this->attribute_ascii_id = $a->ascii_id;
		$this->attribute = $a;
		return $a;
	}
}
