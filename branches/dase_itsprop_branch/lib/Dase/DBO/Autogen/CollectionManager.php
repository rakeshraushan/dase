<?php

require_once 'Dase/DBO.php';

/*
 * DO NOT EDIT THIS FILE
 * it is auto-generated by the
 * script 'bin/class_gen.php
 * 
 */

class Dase_DBO_Autogen_CollectionManager extends Dase_DBO 
{
	function __construct($assoc = false) 
	{
		parent::__construct( 'collection_manager',  array('auth_level','collection_ascii_id','created','created_by_eid','dase_user_eid','expiration'));
		if ($assoc) {
			foreach ( $assoc as $key => $value) {
				$this->$key = $value;
			}
		}
	}
}