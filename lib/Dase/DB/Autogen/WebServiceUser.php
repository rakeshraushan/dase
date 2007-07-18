<?php

require_once 'Dase/DB/Object.php';

class Dase_DB_Autogen_WebServiceUser extends Dase_DB_Object 
{
	function __construct($assoc = false) {
		parent::__construct( 'web_service_user',  array('auth_level','name','token'));
		if ($assoc) {
			foreach ( $assoc as $key => $value) {
				$this->$key = $value;
			}
		}
	}
}