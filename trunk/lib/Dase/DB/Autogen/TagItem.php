<?php

require_once 'Dase/DB/Object.php';

class Dase_DB_Autogen_TagItem extends Dase_DB_Object 
{
	function __construct($assoc = false) {
		parent::__construct( 'tag_item',  array('tag_id','item_id','annotation','sort_order','timestamp','size','p_serial_number','p_collection_ascii_id'));
		if ($assoc) {
			foreach ( $assoc as $key => $value) {
				$this->$key = $value;
			}
		}
	}
}