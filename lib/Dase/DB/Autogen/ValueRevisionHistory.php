<?php

require_once 'Dase/DB/Object.php';

class Dase_DB_Autogen_ValueRevisionHistory extends Dase_DB_Object 
{
	function __construct($assoc = false) {
		parent::__construct( 'value_revision_history',  array('dase_user_eid','deleted_text','added_text','timestamp','item_serial_number','attribute_name','collection_ascii_id'));
		if ($assoc) {
			foreach ( $assoc as $key => $value) {
				$this->$key = $value;
			}
		}
	}
}