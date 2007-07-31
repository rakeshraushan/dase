<?php

Class Dase_Utils 
{

	private function __construct() {}

	public static function camelCaseString($str) {
		$words = preg_split("/[\s_]+/", $str);
		$camel = array_shift($words);
		foreach ($words as $word) {
			$camel .= ucfirst($word);
		}	
		return $camel;
	}

	public static function getVersion() {
		$ver = explode( '.', PHP_VERSION );
		return $ver[0] . $ver[1] . $ver[2];
	}
}

