<?php

require_once 'Dase/DB/Object.php';

class Dase_DB_Autogen_MessageQueue extends Dase_DB_Object 
{
	function __construct($assoc = false) {
		parent::__construct( 'message_queue',  array('created','dase_user_eid','is_active','text'));
		if ($assoc) {
			foreach ( $assoc as $key => $value) {
				$this->$key = $value;
			}
		}
	}
}