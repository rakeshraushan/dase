<?php

require_once 'Dase/DBO.php';

/*
 * DO NOT EDIT THIS FILE
 * it is auto-generated by the
 * script 'bin/class_gen.php
 * 
 */

class Dase_DBO_Autogen_Comment extends Dase_DBO 
{
	function __construct($assoc = false) 
	{
		parent::__construct( 'comment',  array('item_id','p_collection_ascii_id','p_serial_number','text','type','updated','updated_by_eid'));
		if ($assoc) {
			foreach ( $assoc as $key => $value) {
				$this->$key = $value;
			}
		}
	}
}