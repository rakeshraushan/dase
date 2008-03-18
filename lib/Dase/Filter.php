<?php

class Dase_Filter 
{
	public static function filterArray($ar)
	{
		if (Dase_Util::getVersion() >= 520) {
			return filter_var_array($ar, FILTER_SANITIZE_STRING);
		} else {
			foreach ($ar as $k => $v) {
				$ar[$k] = strip_tags($v);
			}
			return $ar;
		}
	}

	public static function filterGetArray()
	{
		if (Dase_Util::getVersion() >= 520) {
			return filter_input_array(INPUT_GET);
		} else {
			$ar = array();
			foreach ($_GET as $k => $v) {
				if (is_array($v)) {
					$v = Dase_Filter::filterArray($v);
					$ar[$k] = $v;
				} else {
					$ar[$k] = trim(strip_tags($v));
				}
			}
			return $ar;
		}
	}

	public static function filterGet($key)
	{
		if (Dase_Util::getVersion() >= 520) {
			return trim(filter_input(INPUT_GET, $key, FILTER_SANITIZE_STRING));
		} else {
			if (isset($_GET[$key])) {
				return trim(strip_tags($_GET[$key]));
			}
		}
	}

	public static function filterPost($key)
	{
		if (Dase_Util::getVersion() >= 520) {
			return trim(filter_input(INPUT_POST, $key, FILTER_SANITIZE_STRING));
		} else {
			if (isset($_POST[$key])) {
				return strip_tags($_POST[$key]);
			}
		}
	}
}

