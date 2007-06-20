<?php

require_once 'Dase/DB/Object.php';

class Dase_DB_Autogen_HtmlInputType extends Dase_DB_Object 
{
	function __construct($assoc = false) {
		parent::__construct( 'html_input_type',  array('name'));
		if ($assoc) {
			foreach ( $assoc as $key => $value) {
				$this->$key = $value;
			}
		}
	}
}