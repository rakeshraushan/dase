<?php

require_once 'Dase/DB/Object.php';

class Dase_DB_Autogen_ItemType extends Dase_DB_Object 
{
	function __construct($assoc = false) {
		parent::__construct( 'item_type',  array('ascii_id','collection_id','description','name','parent_item_type_id','relation_attribute_id'));
		if ($assoc) {
			foreach ( $assoc as $key => $value) {
				$this->$key = $value;
			}
		}
	}
}