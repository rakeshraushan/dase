<?php

require_once 'Dase/DB/Object.php';

class Dase_DB_Autogen_Tag extends Dase_DB_Object 
{
	function __construct($assoc = false) {
		parent::__construct( 'tag',  array('admin_collection_id','ascii_id','background','dase_user_id','description','is_public','master_item_id','name','tag_type_id','timestamp'));
		if ($assoc) {
			foreach ( $assoc as $key => $value) {
				$this->$key = $value;
			}
		}
	}
}