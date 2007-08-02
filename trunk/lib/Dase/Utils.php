<?php

Class Dase_Utils 
{

	private function __construct() {}

	public static function getVersion() {
		$ver = explode( '.', PHP_VERSION );
		return $ver[0] . $ver[1] . $ver[2];
	}
}

