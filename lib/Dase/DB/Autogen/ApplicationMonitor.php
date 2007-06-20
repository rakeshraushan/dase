<?php

require_once 'Dase/DB/Object.php';

class Dase_DB_Autogen_ApplicationMonitor extends Dase_DB_Object 
{
	function __construct($assoc = false) {
		parent::__construct( 'application_monitor',  array('response_time','timestamp','dase_user_eid','server_addr','server_name','request_uri','remote_addr'));
		if ($assoc) {
			foreach ( $assoc as $key => $value) {
				$this->$key = $value;
			}
		}
	}
}