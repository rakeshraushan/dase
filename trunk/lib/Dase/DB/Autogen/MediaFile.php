<?php

require_once 'Dase/DB/Object.php';

class Dase_DB_Autogen_MediaFile extends Dase_DB_Object 
{
	function __construct($assoc = false) {
		parent::__construct( 'media_file',  array('item_id','filename','height','width','mime_type','size','p_serial_number','p_collection_ascii_id','file_size'));
		if ($assoc) {
			foreach ( $assoc as $key => $value) {
				$this->$key = $value;
			}
		}
	}
}