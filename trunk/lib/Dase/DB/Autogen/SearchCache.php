<?php

require_once 'Dase/DB/Object.php';

class Dase_DB_Autogen_SearchCache extends Dase_DB_Object 
{
	function __construct($assoc = false) {
		parent::__construct( 'search_cache',  array('query','timestamp','dase_user_id','attribute_id','collection_id_string','refine','item_id_string','exact_search','is_stale','sort_by','cb_id'));
		if ($assoc) {
			foreach ( $assoc as $key => $value) {
				$this->$key = $value;
			}
		}
	}
}