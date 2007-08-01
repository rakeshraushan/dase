<?php

require_once 'Dase/DB/Object.php';

class Dase_DB_Autogen_Subscription extends Dase_DB_Object 
{
	function __construct($assoc = false) {
		parent::__construct( 'subscription',  array('dase_user_id','tag_id'));
		if ($assoc) {
			foreach ( $assoc as $key => $value) {
				$this->$key = $value;
			}
		}
	}
}