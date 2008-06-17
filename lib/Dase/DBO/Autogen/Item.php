<?php

require_once 'Dase/DBO.php';

/*
 * DO NOT EDIT THIS FILE
 * it is auto-generated by the
 * script 'bin/class_gen.php
 * 
 */

class Dase_DBO_Autogen_Item extends Dase_DBO 
{
	function __construct($assoc = false) 
	{
		parent::__construct( 'item',  array('collection_id','created','item_type_id','serial_number','status','status_id','updated'));
		if ($assoc) {
			foreach ( $assoc as $key => $value) {
				$this->$key = $value;
			}
		}
	}
}