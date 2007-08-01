<?php

require_once 'Dase/DB/Object.php';

class Dase_DB_Autogen_XmlCache extends Dase_DB_Object 
{
	function __construct($assoc = false) {
		parent::__construct( 'xml_cache',  array('collection_id','is_stale','name','other_ident','text','timestamp'));
		if ($assoc) {
			foreach ( $assoc as $key => $value) {
				$this->$key = $value;
			}
		}
	}
}