<?php

require_once 'Dase/DB/Object.php';

class Dase_DB_Autogen_ValueRevisionHistory extends Dase_DB_Object 
{
	function __construct($assoc = false) {
		parent::__construct( 'value_revision_history',  array('added_text','attribute_name','collection_ascii_id','dase_user_eid','deleted_text','item_serial_number','timestamp','unix_timestamp'));
		if ($assoc) {
			foreach ( $assoc as $key => $value) {
				$this->$key = $value;
			}
		}
	}
}