<?php

require_once 'Dase/DB/Object.php';

class Dase_DB_Autogen_Item extends Dase_DB_Object 
{
	function __construct($assoc = false) {
		parent::__construct( 'item',  array('serial_number','collection_id','timestamp','status_id','item_type_id'));
		if ($assoc) {
			foreach ( $assoc as $key => $value) {
				$this->$key = $value;
			}
		}
	}
}