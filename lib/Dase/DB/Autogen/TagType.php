<?php

require_once 'Dase/DB/Object.php';

class Dase_DB_Autogen_TagType extends Dase_DB_Object 
{
	function __construct($assoc = false) {
		parent::__construct( 'tag_type',  array('ascii_id','name'));
		if ($assoc) {
			foreach ( $assoc as $key => $value) {
				$this->$key = $value;
			}
		}
	}
}