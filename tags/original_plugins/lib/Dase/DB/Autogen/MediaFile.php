<?php

require_once 'Dase/DB/Object.php';

class Dase_DB_Autogen_MediaFile extends Dase_DB_Object 
{
	function __construct($assoc = false) {
		parent::__construct( 'media_file',  array('file_size','filename','height','item_id','mime_type','p_collection_ascii_id','p_serial_number','size','width'));
		if ($assoc) {
			foreach ( $assoc as $key => $value) {
				$this->$key = $value;
			}
		}
	}
}