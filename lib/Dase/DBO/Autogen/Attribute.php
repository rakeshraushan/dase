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
	public function __construct($db,$assoc = false) 
	{
		parent::__construct($db,'attribute', array('ascii_id','attribute_name','collection_id','html_input_type','in_basic_search','is_on_list_display','is_public','is_repeatable','is_required','mapped_admin_att_id','modifier_defined_list','modifier_type','sort_order','updated','usage_notes'));
		if ($assoc) {
			foreach ( $assoc as $key => $value) {
				$this->$key = $value;
			}
		}
	}
}