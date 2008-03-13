<?php

class Dase_Url
{

	public static function parseQueryString() {
		//split params into key value pairs AND allow multiple 
		//params w/ same key as an array (like standard CGI)
		$url_params = array();
		if (isset($_SERVER['QUERY_STRING'])) {
			//deal w/ &amp;
			$qs = html_entity_decode($_SERVER['QUERY_STRING']);
			$pairs = explode('&',$qs);
			if (count($pairs)) {
				foreach ($pairs as $pair) {
					if (false !== strpos($pair,'=')) {	
						list($key,$val) = explode('=',$pair);
						if (!isset($url_params[$key])) {
							//not an array
							$url_params[$key] = $val;
						} elseif(is_array($url_params[$key])) {
							//IS an array
							$url_params[$key][] = $val;
						} else {
							//key is set, but it is NOT an array, so make it one!!
							$temp = $url_params[$key];
							$url_params[$key] = array();
							$url_params[$key][] = $temp;
							$url_params[$key][] = $val;
						}
					}
				}
			}
		}
		return $url_params;
	}

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

