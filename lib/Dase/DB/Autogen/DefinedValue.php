<?php

require_once 'Dase/DBO.php';

/*
 * DO NOT EDIT THIS FILE
 * it is auto-generated by the
 * script 'bin/class_gen.php
 * 
 */

class Dase_DB_Autogen_DefinedValue extends Dase_DBO 
{
	function __construct($assoc = false) {
		parent::__construct( 'defined_value',  array('attribute_id','value_text'));
		if ($assoc) {
			foreach ( $assoc as $key => $value) {
				$this->$key = $value;
			}
		}
	}
}