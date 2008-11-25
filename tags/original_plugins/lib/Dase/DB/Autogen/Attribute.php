<?php

require_once 'Dase/DB/Object.php';

class Dase_DB_Autogen_Attribute extends Dase_DB_Object 
{
	function __construct($assoc = false) {
		parent::__construct( 'attribute',  array('ascii_id','atom_element','attribute_name','collection_id','html_input_type_id','in_basic_search','is_on_list_display','is_public','mapped_admin_att_id','sort_order','usage_notes'));
		if ($assoc) {
			foreach ( $assoc as $key => $value) {
				$this->$key = $value;
			}
		}
	}
}