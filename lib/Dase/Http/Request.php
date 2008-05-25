<?php

class Dase_Http_Request
{
	private $url_params = array();
	private $params;
	private $members = array();
	public static $types = array(
		'atom' =>'application/atom+xml',
		'json' =>'application/json',
		'html' =>'text/html',
		'css' =>'text/css',
		'txt' =>'text/plain',
	);
	public $method;
	public $format;
	public $response_mime_type;

	function __construct()
	{
		$this->format = $this->getFormat();
		$this->response_mime_type = self::$types[$this->format];
		$this->method = strtolower($_SERVER['REQUEST_METHOD']);
	}

	function __get($var) 
	{
		$classname = get_class($this);
		$method = 'get'.ucfirst($var);
		if (method_exists($classname,$method)) {
			return $this->{$method}();
		} 
	}

	function getFormat()
	{
		//first check extension
		$pathinfo = pathinfo($this->getPath());
		if (isset($pathinfo['extension']) && $pathinfo['extension']) {
			$ext = $pathinfo['extension'];
			if (isset(self::$types[$ext])) {
				//changes path!  a side effect!
				$this->path = str_replace('.'.$ext,'',$this->path);
				return $ext;
			}
		}
		//next, try 'format=' query param
		if ($this->has('format')) {
			if (isset(self::$types[$this->get('format')])) {
				return $this->get('format');
			}
		}	
		//lastly, look at accept header (conneg)
		$mimeparse = new Mimeparse;
		$mime_match = $mimeparse->best_match(self::$types,$_SERVER['HTTP_ACCEPT']);
		if (in_array($mime_match,self::$types)) {
			return array_search($mime_match,self::$types); //returns format
		}
		//default is html
		return 'html';
	}

	public function get($key,$as_array = false)
	{
		if (!$as_array) {
			//precedence is post,get,url_param,set member
			$value = $this->_filterPost($key) ? $this->_filterPost($key) : $this->_filterGet($key);
			if ($value) {
				return $value;
			} else {
				if (isset($this->params[$key])) {
					return $this->params[$key];
				}
				if (isset($this->members[$key])) {
					return $this->members[$key];
				}
				return false;
			}
		} else {
			if ('POST' == $this->method) {
				return $this->_filterArray($_POST[$key]);
			} else {
				return $this->_getUrlParamsArray($key);
			}
		}
	}

	public function has($key)
	{
		return $this->_filterPost($key) || $this->_filterGet($key) || isset($this->params[$key]);
	}

	public function set($key,$val)
	{
		$this->members[$key] = $val;
	}

	public function setParams($params)
	{
		$this->params = $params;
	}

	private function getUrlParams()
	{
		$this->_getUrlParamsArray('xxxxx');
		return $this->url_params;
	}

	private function _getUrlParamsArray($key)
	{
		if (count($this->url_params)) {
			//meaning we've been here
			if (isset($this->url_params[$key])) {
				return $this->url_params[$key];
			} else {
				return array();
			}
		}
		//allow multiple params w/ same key as an array (like standard CGI)
		//todo: write tests for this
		$url_params = array();
		$url_params[$key] = array();
		//NOTE: urldecode is NOT UTF-8 compatible
		$pairs = explode('&',html_entity_decode(urldecode($_SERVER['QUERY_STRING'])));
		if (count($pairs)) {
			foreach ($pairs as $pair) {
				if (false !== strpos($pair,'=')) {	
					list($k,$v) = explode('=',$pair);
					if (!isset($url_params[$key])) {
						$url_params[$k] = array();
					} 
					$url_params[$k][] = $v;
				} else { //this deals with case of '&' in search term!
					//we still have $k left over from last list ($k,$v) = explode... 
					$last = array_pop($url_params[$k]);
					$url_params[$k][] = $last.'&'.$pair;
				} 
			}
		}
		$this->url_params = $url_params;
		return $url_params[$key];
	}

	public function getUrl() 
	{
		$this->path = $this->path ? $this->path : $this->getPath();
		return $this->path . '?' . $_SERVER['QUERY_STRING'];
	}

	public function getQueryString() 
	{
		return $_SERVER['QUERY_STRING'];
	}

	public function getPath()
	{
		//returns full path w/o domain & w/o query string
		$path = '';
		if (isset($_SERVER['REQUEST_URI'])) {
			$path = $_SERVER['REQUEST_URI'];
			if ('/' != APP_BASE) {
				$path= str_replace(APP_BASE,'',$path);
			}
			$path= trim($path, '/');
			/* Remove the query_string from the URL */
			if ( strpos($path, '?') !== FALSE ) {
				list($path,$query_string )= explode('?', $path);
			}
		}
		return $path;
	}

	private function getUser()
	{
		//return user - if none return 'guest'

		//figure out how to get user here.  
		//it's OK for this to trigger a login screen

		//-http auth?
		//-eid?
		//-cookie?

	}

	private function _filterArray($ar)
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

	private function _filterGet($key)
	{
		if (Dase_Util::getVersion() >= 520) {
			return trim(filter_input(INPUT_GET, $key, FILTER_SANITIZE_STRING));
		} else {
			if (isset($_GET[$key])) {
				return trim(strip_tags($_GET[$key]));
			}
		}
		return false;
	}

	private function _filterPost($key)
	{
		if (Dase_Util::getVersion() >= 520) {
			return trim(filter_input(INPUT_POST, $key, FILTER_SANITIZE_STRING));
		} else {
			if (isset($_POST[$key])) {
				return strip_tags($_POST[$key]);
			}
		}
		return false;
	}
}

