<?php

require_once 'Dase/DB/Object.php';

class Dase_DB_Autogen_SearchCache extends Dase_DB_Object 
{
	function __construct($assoc = false) {
		parent::__construct( 'search_cache',  array('attribute_id','cb_id','collection_id_string','dase_user_id','exact_search','is_stale','item_id_string','query','refine','search_md5','sort_by','timestamp'));
		if ($assoc) {
			foreach ( $assoc as $key => $value) {
				$this->$key = $value;
			}
		}
	}
}