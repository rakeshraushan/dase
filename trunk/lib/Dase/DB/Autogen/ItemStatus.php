<?php

require_once 'Dase/DB/Object.php';

class Dase_DB_Autogen_ItemStatus extends Dase_DB_Object 
{
	function __construct($assoc = false) {
		parent::__construct( 'item_status',  array('status'));
		if ($assoc) {
			foreach ( $assoc as $key => $value) {
				$this->$key = $value;
			}
		}
	}
}