<?php

require_once 'Dase/DB/Object.php';

class Dase_DB_Autogen_Category extends Dase_DB_Object 
{
	function __construct($assoc = false) {
		parent::__construct( 'category',  array('name','is_public','sort_order','collection_id','ascii_id'));
		if ($assoc) {
			foreach ( $assoc as $key => $value) {
				$this->$key = $value;
			}
		}
	}
}