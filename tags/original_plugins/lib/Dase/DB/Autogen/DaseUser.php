<?php

require_once 'Dase/DB/Object.php';

class Dase_DB_Autogen_DaseUser extends Dase_DB_Object 
{
	function __construct($assoc = false) {
		parent::__construct( 'dase_user',  array('backtrack','cb','current_collections','current_search_cache_id','display','eid','has_access_exception','last_action','last_cb_access','last_item','max_items','name','template_composite'));
		if ($assoc) {
			foreach ( $assoc as $key => $value) {
				$this->$key = $value;
			}
		}
	}
}