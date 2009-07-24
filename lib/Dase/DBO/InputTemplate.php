<?php

require_once 'Dase/DBO/Autogen/InputTemplate.php';

class Dase_DBO_InputTemplate extends Dase_DBO_Autogen_InputTemplate 
{
	public function getAttribute()
	{
		$att = new Dase_DBO_Attribute($this->db);
		$att->load($this->attribute_id);
		return $att;
	}

}
