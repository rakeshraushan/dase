<?php

class Dase_Http_Request
{
	public static $types = array(
		'atom' =>'application/atom+xml',
		'cats' =>'application/atomcat+xml',
		'css' =>'text/css',
		'csv' =>'text/csv',
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

	private $eid_is_service_user;
	private $env = array();
	private $members = array();
	private $object_store = array();
	private $params;
	private $url_params = array();
	private $user;

	public function __construct($base_path)
	{
		$env['base_path'] = $base_path;
		$env['protocol'] = isset($_SERVER['HTTPS']) ? 'https' : 'http'; 
		$env['method'] = strtolower($_SERVER['REQUEST_METHOD']);
		$env['_get'] = $_GET;
		$env['_post'] = $_POST;
		$env['_cookie'] = $_COOKIE;
		$env['_files'] = $_FILES;
		$env['htuser'] = isset($_SERVER['PHP_AUTH_USER']) ? $_SERVER['PHP_AUTH_USER'] : '';
		$env['htpass'] = isset($_SERVER['PHP_AUTH_PW']) ? $_SERVER['PHP_AUTH_PW'] : '';
		$env['request_uri'] = $_SERVER['REQUEST_URI'];
		$env['http_host'] =	$_SERVER['HTTP_HOST'];
		$env['server_addr'] = $_SERVER['SERVER_ADDR'];
		$env['query_string'] =	$_SERVER['QUERY_STRING'];
		$env['script_name'] = $_SERVER['SCRIPT_NAME'];
		$env['remote_addr'] = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
		$env['http_user_agent'] = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
		$env['app_root'] = $env['protocol'].'://'.$env['http_host'].dirname($env['script_name']);
		//env is assign to this twice since it needs to be use in other methods
		$this->env = $env;
		$env['format'] = $this->getFormat();
		$env['module'] = $this->getModule(); 
		$env['handler'] = $this->getHandler(); 
		$env['path'] = $this->getPath();
		if ($env['module']) {
			$env['module_root'] = $env['app_root'].'/modules/'.$env['module'];
		} else {
			$env['module_root'] = '';
		}
		$env['response_mime_type'] = self::$types[$env['format']];
		$env['content_type'] = $this->getContentType();
		$env['start_time'] = Dase_Util::getTime();
		$this->env = $env;
	}
	
	public function __get( $var )
	{
		//first env
		if ( array_key_exists($var,$this->env)) {
			return $this->env[$var];
		}
		//second params
		if ( array_key_exists( $var, $this->members ) ) {
			return $this->members[ $var ];
		}
		//third getter
		$classname = get_class($this);
		$method = 'get'.ucfirst($var);
		if (method_exists($classname,$method)) {
			return $this->{$method}();
		}
	}

	public function setAuth($auth_config)
	{
		$this->token = $auth_config['token'];
		$this->ppd_token = $auth_config['ppd_token'];
		$this->service_token = $auth_config['service_token'];
		$this->superusers = isset($auth_config['superuser']) ? $auth_config['superuser'] : array();
	}

	public function getBody()
	{
		return file_get_contents("php://input");
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
			$this->setModule($custom_handlers[$h]);
		}
	}

	public function getElapsed()
	{
		$now = Dase_Util::getTime();
		return round($now - $this->start_time,4);
	}

	public function getLogData()
	{
		$env = $this->env;
		$out = '[method] '.$env['method']."\n";
		$out .= '[remote_addr] '.$env['remote_addr']."\n";
		$out .= '[http_user_agent] '.$env['http_user_agent']."\n";
		$out .= '[app_root] '.$env['app_root']."\n";
		$out .= '[format] '.$env['format']."\n";
		$out .= '[module] '.$env['module']."\n"; 
		$out .= '[handler] '.$env['handler']."\n"; 
		return $out;
	}

	public function setCookie($cookie_type,$value)
	{
		$this->retrieve('cookie')->set($cookie_type,$value);
	}

	public function getCookie($cookie_type)
	{
		return $this->retrieve('cookie')->get($cookie_type,$this->env['_cookie']);
	}

	public function logger() 
	{
		if ($this->retrieve('log')) {
			return $this->retrieve('log');
		} else {
			throw new Dase_Http_Exception('no logger registered with request');
		}
	}

	public function getCacheId()
	{
		//cache buster deals w/ aggressive browser caching.  Not to be used on server (so normalized).
		$query_string = preg_replace("!cache_buster=[0-9]*!i",'cache_buster=stripped',$this->query_string);
		$this->logger()->debug('cache id is '. $this->method.'|'.$this->path.'|'.$this->format.'|'.$query_string);
		return $this->method.'|'.$this->path.'|'.$this->format.'|'.$query_string;
	}

	public function checkCache($ttl=null)
	{
		$cache = $this->retrieve('cache');
		$content = $cache->getData($this->getCacheId(),$ttl,$this->retrieve('log'));
		if ($content) {
			$this->renderResponse($content,false);
		}
	}

	public function setModule($module) 
	{
		$this->env['module'] = $module;
		$this->env['module_root'] = $this->env['app_root'].'/modules/'.$this->env['module'];
	}

	public function getModule()
	{
		$parts = explode('/',trim($this->getPath($this->env),'/'));
		$first = array_shift($parts);
		if ('modules' == $first) {
			if(!isset($parts[0])) {
				$this->renderError(404,'no module specified');
			}
			if(!file_exists($this->env['base_path'].'/modules/'.$parts[0])) {
				$this->renderError(404,'no such module');
			}
			return $parts[0];
		} else {
			return '';
		}
	}

	public function getHandler()
	{
		$parts = explode('/',trim($this->getPath(),'/'));
		$first = array_shift($parts);
		if ('modules' == $first && isset($parts[0])) {
			//so dispatch matching works
			return 'modules/'.$parts[0];
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
		if (isset($_SERVER['CONTENT_TYPE'])) {
			$header = $_SERVER['CONTENT_TYPE'];
		}
		if (isset($_SERVER['HTTP_CONTENT_TYPE'])) {
			$header = $_SERVER['HTTP_CONTENT_TYPE'];
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

	public function getFormat()
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
		if ('get' == $this->env['method']) {
			return 'html';
		}
		return 'default';
	}

	public function getPath($strip_extension=true)
	{
		//returns full path w/o domain & w/o query string
		$path = $this->env['request_uri'];
		if (strpos($path,'..')) { //thwart the wily hacker
			throw new Dase_Http_Exception('no go');	
		}
		$base = trim(dirname($this->env['script_name']),'/');
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

	public function getAll($key)
	{
		return $this->get($key,true);
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
		$pairs = explode('&',html_entity_decode(urldecode($this->query_string)));
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
		return trim($this->path . '?' . $this->query_string,'?');
	}

	public function addQueryString($pairs_string)
	{
		if ($this->query_string) {
			$this->query_string .= "&".$pairs_string;
		} else {
			$this->query_string = "?".$pairs_string;
		}
	}

	public function setQueryStringParam($key,$val)
	{
		$this->query_string = preg_replace("!$key=[^&]*!","$key=$val",$this->query_string,1,$count);
		if (!$count) {
			$this->addQueryString("$key=$val");
		}
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
			$eid = $this->retrieve('cookie')->getEid($this->_cookie);
			break;
		case 'http':
			$eid = $this->getEid();
			break;
		case 'service':
			$eid = $this->getEid(true);
			break;
		case 'none':
			//allows nothing to happen
			return;
		default:
			$eid = $this->retrieve('cookie')->getEid($this->_cookie);
		}

		//eids are always lowercase
		$eid = strtolower($eid);

		if ($eid) {
			$this->user = $this->retrieve('user')->retrieveByEid($eid);
			if ($this->eid_is_service_user) {
				$this->user->is_service_user = true;
			}
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

	public function getEid($check_db=false)
	{
		$request_headers = apache_request_headers();
		$passwords = array();

		if ($this->htuser && $this->htpass) {
			$eid = $this->htuser;
			$passwords[] = substr(md5($this->token.$eid.'httpbasic'),0,12);

			//for service users:
			$service_users = $this->retrieve('config')->getServiceusers();
			//if eid is among service users, get password w/ service_token as salt
			if (isset($service_users[$eid])) {
				$this->logger()->debug('serviceuser request from '.$eid);
				$this->eid_is_service_user = true;
				$passwords[] = md5($this->service_token.$eid);
			}

			$superusers = $this->retrieve('config')->getSuperusers();
			//lets me use the superuser passwd for http work
			if (isset($superusers[$eid])) {
				$passwords[] = $superusers[$eid];
			}

			//this is used for folks needing a quick service pwd to do uploads
			if ($check_db) {
				$u = new Dase_DBO_DaseUser($this->retrieve('db'));
				if ($u->retrieveByEid($eid)) {
					$pass_md5 = md5($this->htpass);
					if ($pass_md5 == $u->service_key_md5) {
						$this->logger()->debug('accepted user '.$eid.' using password '.$this->htpass);
						return $eid;
					}
				}
			}

			if (in_array($this->htpass,$passwords)) {
				$this->logger()->debug('accepted user '.$eid.' using password '.$this->htpass);
				return $eid;
			} else {
				$this->logger()->debug('rejected user '.$eid.' using password '.$this->htpass);
			}
		} else {
			$this->logger()->debug('PHP_AUTH_USER and/or PHP_AUTH_PW not set');
		}
		header('WWW-Authenticate: Basic realm="DASe"');
		header('HTTP/1.1 401 Unauthorized');
		echo "sorry, authorized users only";
		exit;
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
		$response->serveFile($path,$mime_type,$download,$this->base_path);
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
		//todo: put in configuration
		//(fix for polling load balancer)
		if ('service-info' == $this->handler) {
			exit;
		}

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

