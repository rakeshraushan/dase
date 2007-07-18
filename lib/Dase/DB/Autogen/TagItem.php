<?php

require_once 'Dase/DB/Object.php';

class Dase_DB_Autogen_TagItem extends Dase_DB_Object 
{
	function __construct($assoc = false) {
		parent::__construct( 'tag_item',  array('annotation','item_id','p_collection_ascii_id','p_serial_number','size','sort_order','tag_id','timestamp'));
		if ($assoc) {
			foreach ( $assoc as $key => $value) {
				$this->$key = $value;
			}
		}
	}
}