<?php

class Dase_DB_Value extends Dase_DB_Autogen_Value 
{
	public $attribute;

	function getAttribute() {
		$att = new Dase_DB_Attribute;
		$att->load($this->attribute_id);
		$this->attribute = $att;
		return $att;
	}
}
