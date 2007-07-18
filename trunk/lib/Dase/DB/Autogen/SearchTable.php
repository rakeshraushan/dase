<?php

require_once 'Dase/DB/Object.php';

class Dase_DB_Autogen_SearchTable extends Dase_DB_Object 
{
	function __construct($assoc = false) {
		parent::__construct( 'search_table',  array('collection_id','item_id','last_update','status_id','value_text'));
		if ($assoc) {
			foreach ( $assoc as $key => $value) {
				$this->$key = $value;
			}
		}
	}
}