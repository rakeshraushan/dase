<?php

require_once 'Dase/DB/Object.php';

class Dase_DB_Autogen_Collection extends Dase_DB_Object 
{
	function __construct($assoc = false) {
		parent::__construct( 'collection',  array('ascii_id','collection_name','path_to_media_files','description','is_public','display_categories'));
		if ($assoc) {
			foreach ( $assoc as $key => $value) {
				$this->$key = $value;
			}
		}
	}
}