<?php

require_once 'Dase/DB/Object.php';

class Dase_DB_Autogen_Tag extends Dase_DB_Object 
{
	function __construct($assoc = false) {
		parent::__construct( 'tag',  array('name','description','dase_user_id','timestamp','tag_type_id','is_public','background','admin_collection_id','ascii_id','master_item_id'));
		if ($assoc) {
			foreach ( $assoc as $key => $value) {
				$this->$key = $value;
			}
		}
	}
}