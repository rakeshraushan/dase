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
		if (Dase_Utils::getVersion() >= 520) {
			return trim(filter_input(INPUT_GET, $key, FILTER_SANITIZE_STRING));
		} else {
			if (isset($_GET[$key])) {
				return strip_tags($_GET[$key]);
			}
		}
	}

	public static function filterPost($key) {
		if (Dase_Utils::getVersion() >= 520) {
			return trim(filter_input(INPUT_POST, $key, FILTER_SANITIZE_STRING));
		} else {
			if (isset($_POST[$key])) {
				return strip_tags($_POST[$key]);
			}
		}
	}

	public static function getVersion() {
		$ver = explode( '.', PHP_VERSION );
		return $ver[0] . $ver[1] . $ver[2];
	}
}

