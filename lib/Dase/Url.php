<?php

class Dase_Url
{

	public static function get() {
		return Dase_Url::getRequestUrl() . '?' . Dase_Url::getQueryString();
	}

	public static function getQueryString() {
		if (isset($_SERVER['QUERY_STRING'])) {
			return $_SERVER['QUERY_STRING'];
		} else {
			return false;
		}
	}

	public static function getRequestUrl() {
		$request_url = '';
		if (isset($_SERVER['REQUEST_URI'])) {
			$request_url = $_SERVER['REQUEST_URI'];
			if ('/' != APP_BASE) {
				$request_url= str_replace(APP_BASE,'',$request_url);
			}
			$request_url= trim($request_url, '/');
			/* Remove the query_string from the URL */
			if ( strpos($request_url, '?') !== FALSE ) {
				list($request_url,$query_string )= explode('?', $request_url);
			}

		}
		return $request_url;
	}
}

