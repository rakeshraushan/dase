<?php

require_once 'Dase/DB/Object.php';

class Dase_DB_Autogen_Collection extends Dase_DB_Object 
{
	function __construct($assoc = false) {
		parent::__construct( 'collection',  array('........pg.dropped.7........','........pg.dropped.8........','ascii_id','collection_name','description','is_public','path_to_media_files'));
		if ($assoc) {
			foreach ( $assoc as $key => $value) {
				$this->$key = $value;
			}
		}
	}
}
