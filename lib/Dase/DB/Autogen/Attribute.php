<?php

require_once 'Dase/DB/Object.php';

class Dase_DB_Autogen_Attribute extends Dase_DB_Object 
{
	function __construct($assoc = false) {
		parent::__construct( 'attribute',  array('ascii_id','collection_id','attribute_name','usage_notes','sort_order','in_basic_search','is_on_list_display','is_public','html_input_type_id','atom_element','mapped_admin_att_id'));
		if ($assoc) {
			foreach ( $assoc as $key => $value) {
				$this->$key = $value;
			}
		}
	}
}