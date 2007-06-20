<?php

require_once 'Dase/DB/Object.php';

class Dase_DB_Autogen_SearchTable extends Dase_DB_Object 
{
	function __construct($assoc = false) {
		parent::__construct( 'search_table',  array('item_id','collection_id','value_text','status_id','last_update'));
		if ($assoc) {
			foreach ( $assoc as $key => $value) {
				$this->$key = $value;
			}
		}
	}
}