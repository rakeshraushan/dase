<?php

require_once 'Dase/DB/Object.php';

class Dase_DB_Autogen_CollectionManager extends Dase_DB_Object 
{
	function __construct($assoc = false) {
		parent::__construct( 'collection_manager',  array('collection_ascii_id','dase_user_eid','auth_level'));
		if ($assoc) {
			foreach ( $assoc as $key => $value) {
				$this->$key = $value;
			}
		}
	}
}