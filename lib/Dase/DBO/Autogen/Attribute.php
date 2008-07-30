<?php

require_once 'Dase/DBO.php';

/*
 * DO NOT EDIT THIS FILE
 * it is auto-generated by the
 * script 'bin/class_gen.php
 * 
 */

class Dase_DBO_Autogen_Attribute extends Dase_DBO 
{
	function __construct($assoc = false) 
	{
		parent::__construct( 'attribute',  array('is_public','is_on_list_display','in_basic_search','mapped_admin_att_id','sort_order','collection_id','html_input_type','updated','usage_notes','attribute_name','ascii_id'));
		if ($assoc) {
			foreach ( $assoc as $key => $value) {
				$this->$key = $value;
			}
		}
	}
}