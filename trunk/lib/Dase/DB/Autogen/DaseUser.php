<?php

require_once 'Dase/DB/Object.php';

class Dase_DB_Autogen_DaseUser extends Dase_DB_Object 
{
	function __construct($assoc = false) {
		parent::__construct( 'dase_user',  array('eid','name','has_access_exception','cb','last_cb_access','display','max_items','last_item','last_action','current_search_cache_id','current_collections','backtrack','template_composite'));
		if ($assoc) {
			foreach ( $assoc as $key => $value) {
				$this->$key = $value;
			}
		}
	}
}