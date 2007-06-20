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

	public static function filterGet($key) {
		return trim(filter_input(INPUT_GET, $key, FILTER_SANITIZE_STRING));
	}

	public static function filterPost($key) {
		return trim(filter_input(INPUT_POST, $key, FILTER_SANITIZE_STRING));
	}
}

