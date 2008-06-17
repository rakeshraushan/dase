<?php

require_once 'Dase/DBO.php';

/*
 * DO NOT EDIT THIS FILE
 * it is auto-generated by the
 * script 'bin/class_gen.php
 * 
 */

class Dase_DBO_Autogen_Tag extends Dase_DBO 
{
	function __construct($assoc = false) 
	{
		parent::__construct( 'tag',  array('admin_collection_id','ascii_id','background','created','dase_user_id','description','is_public','name','tag_type_id','type'));
		if ($assoc) {
			foreach ( $assoc as $key => $value) {
				$this->$key = $value;
			}
		}
	}
}