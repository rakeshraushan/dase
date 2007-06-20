<?php

require_once 'Dase/DB/Object.php';

class Dase_DB_Autogen_ItemType extends Dase_DB_Object 
{
	function __construct($assoc = false) {
		parent::__construct( 'item_type',  array('collection_id','name','ascii_id','description'));
		if ($assoc) {
			foreach ( $assoc as $key => $value) {
				$this->$key = $value;
			}
		}
	}
}