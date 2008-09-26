<?php

require_once 'Dase/DB/Object.php';

class Dase_DB_Autogen_ApplicationMonitor extends Dase_DB_Object 
{
	function __construct($assoc = false) {
		parent::__construct( 'application_monitor',  array('dase_user_eid','remote_addr','request_uri','response_time','server_addr','server_name','timestamp'));
		if ($assoc) {
			foreach ( $assoc as $key => $value) {
				$this->$key = $value;
			}
		}
	}
}