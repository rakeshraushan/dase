<?php

class Dase_Http_Request
{
	private $members = array();
	private $object_store = array();
	private $params;
	private $url_params = array();
	private $user;

	public static $types = array(
		'atom' =>'application/atom+xml',
		'cats' =>'application/atomcat+xml',
		'css' =>'text/css',
		'default' =>'text/html',
		'gif' =>'image/gif',
		'html' =>'text/html',
		'jpg' =>'image/jpeg',
		'json' =>'application/json',
		'mov' =>'video/quicktime',
		'mp3' =>'audio/mpeg',
		'pdf' =>'application/pdf',
		'txt' =>'text/plain',
		'uris' =>'text/uri-list',
		'uri' =>'text/uri-list',
		'xhtml' =>'application/xhtml+xml',
	);
	public $content_type;
	public $error_message;
	public $format;
	public $handler;
	public $method;
	public $module;
	public $path;
	public $query_string;
	public $response_mime_type;
	public $resource;
	public $protocol;
	public $app_root;
	public $base_path;
	private $_server;
	private $_get;
	private $_post;
	private $_cookie;

	public function __construct($base_path,$dase_http_auth)
	{
		$this->base_path = $base_path;
		$this->_files = $_FILES;
		$this->dase_http_auth = $dase_http_auth;
		$this->init(); //wraps request superglobals and sets htuser & htpass on auth obj

		$this->format = $this->getFormat();
		$this->handler = $this->getHandler(); //**ALSO sets $this->module if this is a module request** 
		$this->path = $this->getPath();
		$this->protocol = !isset($this->_server['HTTPS']) ? 'http://' : 'https://'; 
		$this->app_root =
			trim($this->protocol.$this->_server['HTTP_HOST'].'/'.
			trim(dirname($this->_server['SCRIPT_NAME']),'/'),'/');
		if ($this->module) { //this->module is set in getHandler method
			$this->module_root = $this->app_root.'/modules/'.$this->module;
		}
		$this->response_mime_type = self::$types[$this->format];
		$this->query_string = $this->getQueryString();
		$this->content_type = $this->getContentType();
		$this->start_time = Dase_Util::getTime();
	}

	public function init()
	{
		if (isset($_SERVER['REQUEST_METHOD'])) {
			$this->_server = $_SERVER;
			//wrap superglobals
			$this->_get = $_GET;
			$this->_post = $_POST;
			$this->_cookie = $_COOKIE;
			$this->method = strtolower($_SERVER['REQUEST_METHOD']);
			$htuser = isset($_SERVER['PHP_AUTH_USER']) ? $_SERVER['PHP_AUTH_USER'] : '';
			$htpass = isset($_SERVER['PHP_AUTH_PW']) ? $_SERVER['PHP_AUTH_PW'] : '';
		} else {
			$this->_server['REQUEST_URI'] = '';
			$this->_server['HTTP_HOST'] = 'localhost';
			$this->_server['SERVER_ADDR'] = '127.0.0.1';
			$this->_server['QUERY_STRING'] = '';
			$this->_server['SCRIPT_NAME'] = '';
			//command line
			foreach ($_SERVER['argv'] as $arg) {
				if (strpos($arg,'=')) {
					list($key,$val) = explode('=',$arg);
					$this->members[$key] = $val;
				}
			}
			$htuser = $this->get('htuser');
			$htpass = $this->get('htpass');
		}
		$this->dase_http_auth->setUser($htuser,$htpass);
	}

	public function initPlugin($custom_handlers)
	{
		if ($this->module) { 
			return; 
		}
		$h = $this->handler;
		//simply reimplement any handler as a module
		if (isset($custom_handlers[$h])) {
			if(!file_exists($this->base_path.'/modules/'.$custom_handlers[$h])) {
				$this->renderError(404,'no such module');
			}
			$this->logger()->info('**PLUGIN ACTIVATED**: handler:'.$h.' module:'.$custom_handlers[$h]);
			$this->module = $this->custom_handlers[$h];
		}
	}

	public function getRemoteAddr()
	{
		if (isset($this->_server['REMOTE_ADDR'])) {
			return $this->_server['REMOTE_ADDR'];
		}	
	}

	public function getServerVars() 
	{
		return $this->_server;
	}

	public function getElapsed()
	{
		$now = Dase_Util::getTime();
		return round($now - $this->start_time,4);
	}

	public function getLogData()
	{
		$string = "\nREQUEST:\n";
		$string .= "[format] => $this->format\n";
		$string .= "[handler] => $this->handler\n";
		$string .= "[method] => $this->method\n";
		$string .= "[module] => $this->module\n";
		$string .= "[path] => $this->path\n";
		$string .= "[protocol] => $this->protocol\n";
		$string .= "[app_root] => $this->app_root\n";
		$string .= "[url] => $this->url\n";
		$string .= "[response_mime_type] => $this->response_mime_type\n";
		$string .= "[query_string] => $this->query_string\n";
		$string .= "[content_type] => $this->content_type\n";
		$string .= "[pid] => ".getmypid()."\n";
		if (isset($this->_server['HTTP_USER_AGENT'])) {
			$string .= "[http_user_agent] => ".$this->_server['HTTP_USER_AGENT']."\n";
		}
		if (isset($this->_server['REMOTE_ADDR'])) {
			$string .= "[remote_addr] => ".$this->_server['REMOTE_ADDR']."\n";
		}
		$string .= "[resource] => $this->resource\n";
		if ($this->error_message) {
			$string .= "[error message] => $this->error_message\n";
		}
		return $string;
	}

	public function logger() {
		$log = $this->retrieve('log');
		if ($log) {
			return $log;
		} else {
			throw new Dase_Http_Exception('no logger registered with request');
		}
	}

	public function __get($var) 
	{
		if ( array_key_exists( $var, $this->members ) ) {
			return $this->members[ $var ];
		}
		$classname = get_class($this);
		$method = 'get'.ucfirst($var);
		if (method_exists($classname,$method)) {
			return $this->{$method}();
		} 
	}

	public function getCacheId()
	{
		$query_string = $this->getQueryString();
		if ($query_string) {
			//cache buster deals w/ aggressive browser caching.  Not to be used on server (so normalized).
			$query_string = preg_replace("!cache_buster=[0-9]*!i",'cache_buster=stripped',$query_string);
		}
		$this->logger()->debug('cache id is '. $this->method.'|'.$this->path.'|'.$this->format.'|'.$query_string);
		return $this->method.'|'.$this->path.'|'.$this->format.'|'.$query_string;
	}

	public function checkCache($ttl=null)
	{
		$cache = $this->retrieve('cache');
		$content = $cache->getData($this->getCacheId(),$ttl);
		if ($content) {
			$this->renderResponse($content,false);
		}
	}

	public function getHandler()
	{
		$parts = explode('/',trim($this->getPath(),'/'));
		$first = array_shift($parts);
		if ('modules' == $first) {
			if(!isset($parts[0])) {
				$this->renderError(404,'no module specified');
			}
			if(!file_exists(DASE_PATH.'/modules/'.$parts[0])) {
				$this->renderError(404,'no such module');
			}
			$this->module = $parts[0];
			//so dispatch matching works
			return 'modules/'.$this->module;
		} else {
			return $first;
		}
	}

	public function getHeaders() 
	{
		//note: will ONLY work w/ apache (OK by me!)
		return apache_request_headers();
	}

	public function getHeader($name)
	{
		$headers = $this->getHeaders();
		if (isset($headers[$name])) {
			return $headers[$name];
		} else {
			return false;
		}
	}

	public function getContentType() 
	{
		if (isset($this->_server['CONTENT_TYPE'])) {
			$header = $this->_server['CONTENT_TYPE'];
		}
		if (isset($this->_server['HTTP_CONTENT_TYPE'])) {
			$header = $this->_server['HTTP_CONTENT_TYPE'];
		}
		if (isset($header)) {
			list($type,$subtype,$params) = Dase_Media::parseMimeType($header);
			if (isset($params['type'])) {
				return $type.'/'.$subtype.';type='.$params['type'];
			} else {
				return $type.'/'.$subtype;
			}
		}
	}

	public function getFormat($types = null)
	{
		//first check extension
		$pathinfo = pathinfo($this->getPath(false));
		if (isset($pathinfo['extension']) && $pathinfo['extension']) {
			$ext = $pathinfo['extension'];
			if (isset(self::$types[$ext])) {
				return $ext;
			}
		}
		//next, try 'format=' query param
		if ($this->has('format')) {
			if (isset(self::$types[$this->get('format')])) {
				return $this->get('format');
			}
		}	
		//default is html for get requests
		if ('get' == $this->method) {
			return 'html';
		}
		return 'default';
	}

	public function get($key,$as_array = false)
	{
		$post = $this->_post;
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
				if (isset($this->url_params[$key])) {
					//necessary for late-set url_params like when we pass "original search" in
					return $this->url_params[$key][0]; //'cause it is an array
				}
				return false;
			}
		} else {
			if ('post' == $this->method) {
				if (isset($post[$key])) {
					if (is_array($post[$key])) {
						//need to implement the value[] for this to work
						return $this->_filterArray($post[$key]);
					} else {
						return array(strip_tags($post[$key]));
					}
				}
			} else {
				return $this->_getUrlParamsArray($key);
			}
		}
	}

	public function has($key)
	{
		return $this->_filterPost($key) || 
			$this->_filterGet($key) || 
			isset($this->params[$key]) ||
			isset($this->members[$key]) ||
			isset($this->url_params[$key]); //necessary for late-set url_params
	}

	public function set($key,$val)
	{
		$this->members[$key] = $val;
	}

	public function store($key,$object)
	{
		$this->object_store[$key] = $object;
	}

	public function retrieve($key)
	{
		if (isset($this->object_store[$key])) {
			return $this->object_store[$key];
		} else {
			return false;
		}
	}

	/** allows multiple values for a key */
	public function setUrlParam($key,$val)
	{
		$this->_getUrlParamsArray($key); //presetting avoids trouncing 
		if (!isset($this->url_params[$key])) {
			$this->url_params[$key] = array();
		} 
		$this->url_params[$key][] = $val;
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
		$pairs = explode('&',html_entity_decode(urldecode($this->_server['QUERY_STRING'])));
		if (count($pairs) && $pairs[0]) {
			foreach ($pairs as $pair) {
				if (false !== strpos($pair,'=')) {	
					list($k,$v) = explode('=',$pair);
					if (!isset($url_params[$key])) {
						$url_params[$k] = array();
					} 
					$url_params[$k][] = $v;
				} else { //this deals with case of '&' in search term!
					//like search?q=horse&q=red & green
					//we still have $k left over from last list ($k,$v) = explode... 
					if (isset($k)) {
						$last = array_pop($url_params[$k]);
						$url_params[$k][] = $last.'&'.$pair;
					}
				} 
			}
		}
		$this->url_params = $url_params;
		return $url_params[$key];
	}

	public function getUrl() 
	{
		$this->path = $this->path ? $this->path : $this->getPath();
		return trim($this->path . '?' . $this->getQueryString(),'?');
	}

	public function getQueryString() 
	{
		if (!$this->query_string) {
			$this->query_string = $this->_server['QUERY_STRING'];
		}
		return $this->query_string;
	}

	public function addQueryString($pairs_string)
	{
		$this->query_string = $this->getQueryString();
		if ($this->query_string) {
			$this->query_string .= "&".$pairs_string;
		} else {
			$this->query_string = "?".$pairs_string;
		}
	}

	public function setQueryStringParam($key,$val)
	{
		$this->query_string = $this->getQueryString();
		$this->query_string = preg_replace("!$key=[^&]*!","$key=$val",$this->query_string,1,$count);
		if (!$count) {
			$this->addQueryString("$key=$val");
		}
	}

	public function getPath($strip_extension=true)
	{
		//returns full path w/o domain & w/o query string
		$path = $this->_server['REQUEST_URI'];
		if (strpos($path,'..')) { //thwart the wily hacker
			throw new Dase_Http_Exception('no go');	
		}
		$base = trim(dirname($this->_server['SCRIPT_NAME']),'/');
		$path= preg_replace("!$base!",'',$path,1);
		$path= trim($path, '/');
		/* Remove the query_string from the URL */
		if ( strpos($path, '?') !== FALSE ) {
			list($path,$query_string )= explode('?', $path);
		}
		if ($strip_extension) {
			if (strpos($path,'.') !== false) {
				$parts = explode('.', $path);
				$ext = array_pop($parts);
				if (isset(Dase_Http_Request::$types[$ext])) {
					$path = join('.',$parts);
				} else {	
					//path remains what it originally was
				}
			}
		}
		return $path;
	}

	public function setUser($user)
	{
		$this->user = $user;
	}

	public function getUser($auth='cookie',$force_login=true)
	{
		if ($this->user) {
			return $this->user;
		}

		//allow auth type to be forced w/ query param
		if ($this->has('auth')) {
			$auth = $this->get('auth');
		}

		switch ($auth) {
		case 'cookie':
			$eid = $this->retrieve('cookie')->getEid();
			break;
		case 'http':
			$eid = $this->dase_http_auth->getEid($this->logger());
			break;
		case 'service':
			$eid = $this->dase_http_auth->getEid($this->logger(),true);
			break;
		case 'none':
			//allows nothing to happen
			return;
		default:
			$eid = $this->retrieve('cookie')->getEid();
		}

		//eids are always lowercase
		$eid = strtolower($eid);

		if ($eid) {
			$this->user = $this->retrieve('dbuser')->retrieveByEid($eid);
			return $this->user;
		} else {
			if (!$force_login) { return; }
			if ('html' == $this->format) {
				$params['target'] = $this->url;
				$this->renderRedirect('login/form',$params);
			} else {
				$this->renderError(401,'unauthorized');
			}
		}
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
		$get = $this->_get;
		if (Dase_Util::getVersion() >= 520) {
			//fix this!! need to wrap filter_input
			return trim(filter_input(INPUT_GET, $key, FILTER_SANITIZE_STRING));
		} else {
			if (isset($get[$key])) {
				return trim(strip_tags($get[$key]));
			}
		}
		return false;
	}

	private function _filterPost($key)
	{
		$post = $this->_post;
		if (Dase_Util::getVersion() >= 520) {
			//fix this!! need to wrap filter_input
			return trim(filter_input(INPUT_POST, $key, FILTER_SANITIZE_STRING));
		} else {
			if (isset($post[$key])) {
				return strip_tags($post[$key]);
			}
		}
		return false;
	}

	public function renderResponse($content,$set_cache=true,$status_code=null)
	{
		$response = new Dase_Http_Response($this);
		if ('get' != $this->method) {
			$set_cache = false;
		}
		$response->render($content,$set_cache,$status_code);
		exit;
	}

	public function renderOk($msg='')
	{
		$response = new Dase_Http_Response($this);
		$response->ok($msg);
		exit;
	}

	public function serveFile($path,$mime_type,$download=false)
	{
		//$this->logger()->debug('serving '.$path.' as '.$mime_type);
		$response = new Dase_Http_Response($this);
		$response->serveFile($path,$mime_type,$download);
		exit;
	}

	public function renderRedirect($path='',$params=null)
	{
		$response = new Dase_Http_Response($this);
		$response->redirect($path,$params);
		exit;
	}

	public function renderError($code,$msg='')
	{
		$response = new Dase_Http_Response($this);
		$response->error($code,$msg);
		exit;
	}

	public function renderAtomError($code,$msg='')
	{
		$response = new Dase_Http_Response($this);
		$response->atomError($code,$msg);
		exit;
	}
}

