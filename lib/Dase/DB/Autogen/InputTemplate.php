<?php

require_once 'Dase/DB/Object.php';

class Dase_DB_Autogen_InputTemplate extends Dase_DB_Object 
{
	function __construct($assoc = false) {
		parent::__construct( 'input_template',  array('attribute_id','collection_manager_id'));
		if ($assoc) {
			foreach ( $assoc as $key => $value) {
				$this->$key = $value;
			}
		}
	}
}