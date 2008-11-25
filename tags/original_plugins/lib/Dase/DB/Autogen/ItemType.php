<?php

require_once 'Dase/DB/Object.php';

class Dase_DB_Autogen_ItemType extends Dase_DB_Object 
{
	function __construct($assoc = false) {
		parent::__construct( 'item_type',  array('........pg.dropped.6........','........pg.dropped.7........','ascii_id','collection_id','description','name'));
		if ($assoc) {
			foreach ( $assoc as $key => $value) {
				$this->$key = $value;
			}
		}
	}
}