<?php

require_once 'Dase/DBO.php';

/*
 * DO NOT EDIT THIS FILE
 * it is auto-generated by the
 * script 'bin/class_gen.php
 * 
 */

class Dase_DBO_Autogen_ValueRevisionHistory extends Dase_DBO 
{
	function __construct($assoc = false) 
	{
		parent::__construct( 'value_revision_history',  array('added_text','attribute_name','collection_ascii_id','dase_user_eid','deleted_text','item_serial_number','timestamp'));
		if ($assoc) {
			foreach ( $assoc as $key => $value) {
				$this->$key = $value;
			}
		}
	}
}