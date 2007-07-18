<?php

require_once 'Dase/DB/Object.php';

class Dase_DB_Autogen_Item extends Dase_DB_Object 
{
	function __construct($assoc = false) {
		parent::__construct( 'item',  array('collection_id','item_type_id','serial_number','status_id','timestamp'));
		if ($assoc) {
			foreach ( $assoc as $key => $value) {
				$this->$key = $value;
			}
		}
	}
}