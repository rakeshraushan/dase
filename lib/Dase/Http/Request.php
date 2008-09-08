<?php

class Dase_Http_Request
{
	private $members = array();
	private $params;
	private $url_params = array();
	private $user;

	public static $types = array(
		'atom' =>'application/atom+xml',
		'json' =>'application/json',
		'html' =>'text/html',
		'default' =>'text/html',
		'css' =>'text/css',
		'txt' =>'text/plain',
		'jpg' =>'image/jpeg',
		'gif' =>'image/gif',
		'pdf' =>'application/pdf',
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

	function __construct()
	{
		$this->method = strtolower($_SERVER['REQUEST_METHOD']);
		$this->format = $this->getFormat();
		$this->handler = $this->getHandler(); //**ALSO sets $this->module if this is a module request** 
		$this->path = $this->getPath();
		$this->response_mime_type = self::$types[$this->format];
		$this->query_string = $this->getQueryString();
		$this->content_type = $this->getContentType();

		if (!$this->handler) {
			$this->renderRedirect(Dase_Config::get('default_handler'));
		}
	}

	function __toString()
	{
		$string = "\nREQUEST:\n";
		$string .= "[format] => $this->format\n";
		$string .= "[handler] => $this->handler\n";
		$string .= "[method] => $this->method\n";
		$string .= "[module] => $this->module\n";
		$string .= "[path] => $this->path\n";
		$string .= "[response_mime_type] => $this->response_mime_type\n";
		$string .= "[query_string] => $this->query_string\n";
		$string .= "[content_type] => $this->content_type\n";
		$string .= "[resource] => $this->resource\n";
		if ($this->error_message) {
			$string .= "[error message] => $this->error_message\n";
		}
		return $string;
	}

	function __get($var) 
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
			$query_string = preg_replace("!(\?|&)cache_buster=[^&]*!i",'',$query_string);
		}
		Dase_Log::debug('cache id is '. $this->method.'|'.$this->path.'|'.$this->format.'|'.$query_string);
		//todo: this is a *bug* -- when we have multiple params w/ same key
		//this only get the last one! --need to use query string instead
		return $this->method.'|'.$this->path.'|'.$this->format.'|'.$query_string;
	}

	public function checkCache($ttl=null)
	{
		$cache = Dase_Cache::get($this->getCacheId());
		$content = $cache->getData($ttl);
		if ($content) {
			$this->renderResponse($content,false);
		}
	}

	function getHandler()
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
			//here's the entire plugin architecture
			//simply reimplement any handler as a module
			$plugins = Dase_Config::get('handler');
			if (isset($plugins[$first])) {
				if(!file_exists(DASE_PATH.'/modules/'.$plugins[$first])) {
					$this->renderError(404,'no such module');
				}
				Dase_Log::info('**PLUGIN ACTIVATED**: handler:'.$first.' module:'.$plugins[$first]);
				$this->module = $plugins[$first];
			}
			return $first;
		}
	}

	function getHeaders() 
	{
		//note: will ONLY work w/ apache (OK by me!)
		return apache_request_headers();
	}

	function getHeader($name)
	{
		$headers = $this->getHeaders();
		if (isset($headers[$name])) {
			return $headers[$name];
		} else {
			return false;
		}
	}

	function getContentType() 
	{
		if (isset($_SERVER['CONTENT_TYPE'])) {
			$header = $_SERVER['CONTENT_TYPE'];
		}
		if (isset($_SERVER['HTTP_CONTENT_TYPE'])) {
			$header = $_SERVER['HTTP_CONTENT_TYPE'];
		}
		if (isset($header)) {
			$parser = new Mimeparse;
			list($type,$subtype,$params) = $parser->parse_mime_type($header);
			if (isset($params['type'])) {
				return $type.'/'.$subtype.';type='.$params['type'];
			} else {
				return $type.'/'.$subtype;
			}
		}
	}

	function getFormat($types = null)
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
		//lastly, look at accept header (conneg)
		//really, this should wait and the resource
		//should offer format options (instead, as we do
		//here, of just supplying a pre-defined set)
		if (isset($_SERVER['HTTP_ACCEPT'])) {
			$mimeparse = new Mimeparse;
			$types = $types ? $types : self::$types;
			$mime_match = $mimeparse->best_match($types,$_SERVER['HTTP_ACCEPT']);
			if (in_array($mime_match,$types)) {
				return array_search($mime_match,self::$types); //returns format
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
				if (isset($_POST[$key])) {
					if (is_array($_POST[$key])) {
						//need to implement the value[] for this to work
						return $this->_filterArray($_POST[$key]);
					} else {
						return array(strip_tags($_POST[$key]));
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
		$pairs = explode('&',html_entity_decode(urldecode($_SERVER['QUERY_STRING'])));
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
		return trim($this->path . '?' . $this->getQueryString(),'?');
	}

	public function getQueryString() 
	{
		if (!$this->query_string) {
			$this->query_string = $_SERVER['QUERY_STRING'];
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

	public function getPath($strip_extension=true)
	{
		//returns full path w/o domain & w/o query string
		$path = $_SERVER['REQUEST_URI'];
		if (strpos($path,'..')) { //thwart the wily hacker
			//note: php does this already (??)
			Dase::error(401);
		}
		$path= str_replace(APP_BASE,'',$path);
		$path= trim($path, '/');
		/* Remove the query_string from the URL */
		if ( strpos($path, '?') !== FALSE ) {
			list($path,$query_string )= explode('?', $path);
		}
		if ($strip_extension) {
			if (strpos($path,'.') !== false) {
				list($path,$ext )= explode('.', $path);
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
			$eid = Dase_Cookie::getEid();
			break;
		case 'http':
			$eid = Dase_Http_Auth::getEid();
			break;
		case 'none':
			//allows nothing to happen
			return;
		default:
			$eid = Dase_Cookie::getEid();
		}

		if ($eid) {
			return $this->getDbUser($eid);
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

	private function getDbUser($eid) {
		$db = Dase_DB::get();
		$sql = "
			SELECT * FROM dase_user
			WHERE lower(eid) = ?
			";	
		$sth = $db->prepare($sql);
		if ($sth->execute(array($eid))) {
			$this->user = new Dase_DBO_DaseUser($sth->fetch());
			return $this->user;
		} else {
			return false;
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
}

