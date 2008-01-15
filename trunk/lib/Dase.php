<?php

class Dase 
{
	private static $instance;
	public $base_url= '';       
	public $collection;
	public $query_string = '';    
	public $request_url = '';    
	public $response_mime_type = '';
	public static $user;
	public $url_params = array();    

	public function __construct() {}

	//singleton
	public static function instance() {
		if (empty( self::$instance )) {
			self::$instance = new Dase();
		}
		return self::$instance;
	}

	public static function getConf($key) {
		$conf = array();
		include(DASE_CONFIG);
		if (isset($conf[$key])) {
			return $conf[$key];
		} else {
			throw new Exception("no such configuration key: $key");
		}
	}

	public static function log($logfile,$msg) {
		$date = date(DATE_W3C);
		$msg = "$date : $msg\n";
		if(file_exists(DASE_PATH . "/log/{$logfile}.log")) {
			file_put_contents(DASE_PATH ."/log/{$logfile}.log",$msg,FILE_APPEND);
		}
	}

	public static function basicHttpAuth() {
		//from php cookbook 2nd ed. p 240
		if (isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW'])) {
			if (('dase2' == $_SERVER['PHP_AUTH_USER']) && ('api' == $_SERVER['PHP_AUTH_PW'])) {
				return;
			}
		}
		header('WWW-Authenticate: Basic realm="DASe"');
		header('HTTP/1.1 401 Unauthorized');
		echo "sorry, authorized users only";
		exit;
	}

	public function checkUser($auth = 'user',$collection_ascii_id = '',$eid = '') {
		switch ($auth) {
		case 'none':
			//no authorization required
			break;
		case 'user':
			//this means the user has read access to an access-controlled
			//collection OR the are a registed user (any user) accessing a 
			//public collection
			self::$user = new Dase_User();
			if ($collection_ascii_id) {
				// By having the collection_ascii_id in the URL
				// required for collection activity, we can safely
				// protect non-public collections!! 
				if (!self::$user->checkAuth($collection_ascii_id,'read')) {
					Dase::error(401);
				}
			}
			break;
		case 'superuser':
			break;
			self::$user = new Dase_User();
			//only folks whose EID is in config.php as superuser
			//this is for application monitoring and management
			if (!in_array(self::$user->eid,Dase::getConf('superuser'))) {
				Dase::error(401);
			}
			break;
		case 'admin':
			//the checkAuth methods use the collection manager
			//table to look-up privilege level
			self::$user = new Dase_User();
			if (self::$user->eid != $eid) {
				Dase::error(401);
			}
			if (!self::$user->checkAuth($collection_ascii_id,'admin')) {
				Dase::error(401);
			}
			break;
		case 'write':
			self::$user = new Dase_User();
			if (!self::$user->checkAuth($collection_ascii_id,'write')) {
				Dase::error(401);
			}
			break;
		case 'read':
			self::$user = new Dase_User();
			if (!self::$user->checkAuth($collection_ascii_id,'read')) {
				Dase::error(401);
			}
			break;
		case 'http':
			//HTTP basic auth is the prefered (simple) auth method
			//when other computers are interacting with resources
			//it also provides an extra 'layer' on top of collection
			//admin privileges
			Dase::basicHttpAuth();
			break;
		case 'token':
			//token-based auth is the prefered method when DASe is
			//requesting AND serving a resource as in the case of
			//xml and atom data source docs
			if (Dase::filterGet('token') != md5(Dase::getConf('token'))) {
				Dase::error(401);
			}
			break;
		case 'eid':
			//the authorized user eid and the eid in the url
			//must match for this to go through
			self::$user = new Dase_User();
			if (self::$user->eid != $eid) {
				Dase::error(401);
			}
			break;
		default:
			Dase::error(404);
		}
	}

	public static function getUser() {
		if (self::$user) {
			return self::$user;
		}
	}

	public static function getCurrentCollections() {
		if (self::$user) {
			$current_collections = self::$user->current_collections;
			if ($current_collections) {
				return explode(',',$current_collections);
			}
		}
	}

	public static function filterArray($ar) {
		if (Dase_Util::getVersion() >= 520) {
			return filter_var_array($ar, FILTER_SANITIZE_STRING);
		} else {
			foreach ($ar as $k => $v) {
				$ar[$k] = strip_tags($v);
			}
			return $ar;
		}
	}

	public static function filterGetArray() {
		if (Dase_Util::getVersion() >= 520) {
			return filter_input_array(INPUT_GET);
		} else {
			$ar = array();
			foreach ($_GET as $k => $v) {
				if (is_array($v)) {
					$v = Dase::filterArray($v);
					$ar[$k] = $v;
				} else {
					$ar[$k] = trim(strip_tags($v));
				}
			}
			return $ar;
		}
	}

	public static function filterGet($key) {
		if (Dase_Util::getVersion() >= 520) {
			return trim(filter_input(INPUT_GET, $key, FILTER_SANITIZE_STRING));
		} else {
			if (isset($_GET[$key])) {
				return trim(strip_tags($_GET[$key]));
			}
		}
	}

	public static function filterPost($key) {
		if (Dase_Util::getVersion() >= 520) {
			return trim(filter_input(INPUT_POST, $key, FILTER_SANITIZE_STRING));
		} else {
			if (isset($_POST[$key])) {
				return strip_tags($_POST[$key]);
			}
		}
	}

	public static function compileRoutes() {
		// No cache: ~115 req/sec
		// File Based Cache: ~375 req/sec
		$routes = array(); 
		$cache = new Dase_FileCache('routes');
		if ($cache->get()) {
			eval($cache->get());
		} else {
			//xslt pipeline:
			$rx = new Dase_Xslt(DASE_PATH."/inc/routes2map.xsl",DASE_PATH."/inc/routes.xml");
			$rp = new Dase_Xslt(DASE_PATH."/inc/xml2php.xsl",$rx->transform());
			$cache->set($rp->transform());
			eval($cache->get());
		}
		return $routes;
	}

	function parseQuery($qs) {
		$url_params = array();
		$pairs = explode('&',$qs);
		if (count($pairs)) {
			foreach ($pairs as $pair) {
				if (false !== strpos($pair,'=')) {	
					list($key,$val) = explode('=',$pair);
					//NEED TO SANITIZE HERE!!!!!!!!!!!!!!!!!!
					// automatically creates an arry if there is
					// more than one of the key
					if (!isset($url_params[$key])) {
						$url_params[$key] = $val;
					} elseif(is_array($url_params[$key])) {
						$url_params[$key][] = $val;
					} else {
						$temp = $url_params[$key];
						$url_params[$key] = array();
						$url_params[$key][] = $temp;
						$url_params[$key][] = $val;
					}
				}
			}
			$this->url_params = $url_params;
			return $url_params;
		}
	}

	public static function getRequestUrl() {
		// from habari code
		$request_url= ( isset($_SERVER['REQUEST_URI']) 
			? $_SERVER['REQUEST_URI'] 
			: $_SERVER['SCRIPT_NAME'] . 
			( isset($_SERVER['PATH_INFO']) 
			? $_SERVER['PATH_INFO'] 
			: '') . 
			( (isset($_SERVER['QUERY_STRING']) && ($_SERVER['QUERY_STRING'] != '')) 
			? '?' . $_SERVER['QUERY_STRING'] 
			: ''));
		return $request_url;
	}

	public static function run() {
		$dase = Dase::instance();

		/* Strip out the base URL from the requested URL */
		if ('/' != APP_BASE) {
			$request_url= str_replace(APP_BASE,'',Dase::getRequestUrl());
		}

		/* Remove the query_string from the URL */
		if ( strpos($request_url, '?') !== FALSE ) {
			list($request_url,$query_string )= explode('?', $request_url);
		}

		if (isset($query_string) && $query_string) {
			$dase->query_string = $query_string;
			$url_params = $dase->parseQuery(urldecode($query_string));
		} else {
			$query_string = '';
			$dase->query_string = '';
		}

		/* Trim off any leading or trailing slashes */
		$request_url= trim($request_url, '/');

		$dase->request_url = $request_url;

		$matches = array();
		$params = array();

		$routes = Dase::compileRoutes();
		//note: there is only ONE method on a request
		//so that is the only route map we need to traverse
		$method = strtolower($_SERVER['REQUEST_METHOD']);
		foreach ($routes[$method] as $regex => $conf_array) {
			if (preg_match("!$regex!",$request_url,$matches)) {
				//if debug in force, log action
				if (defined('DEBUG')) {
					Dase::log('standard',$regex . " => " . $conf_array['action']);
				}
				$caps = array();
				if (isset($conf_array['caps'])) {
					$caps = explode('/',$conf_array['caps']);
				}
				$params = array();
				if (isset($conf_array['params'])) {
					$params = explode('/',$conf_array['params']);
				}
				$params = array_merge($caps,$params);
				$module_prefix = '';
				if (isset($conf_array['prefix'])) {
					$module_prefix = $conf_array['prefix'];
					define('MODULE_PATH',DASE_PATH . $module_prefix);
					define('MODULE_ROOT',APP_ROOT . $module_prefix);
					ini_set('include_path',ini_get('include_path').':'.MODULE_PATH.'/lib');
				}
				if (isset($matches[1])) { // i.e. at least one paramenter
					array_shift($matches);
					$clean_matches = Dase::filterArray($matches);
					//match param value to its param key
					if (count($params) == count($clean_matches)) {
						$params = array_combine($params,$clean_matches);
					} else {
						print_r($params);
						print_r($clean_matches);
						die ("routes error");
					}
				}
				if (Dase::filterGet('debug_route')) {
					//use this to make a handy debug bookmarklet!
					echo "$regex => {$conf_array['action']}";
					exit;
				}

				$collection_ascii_id = '';
				if (isset($conf_array['collection'])) {
					$collection_ascii_id = $conf_array['collection'];
				} elseif (isset($params['collection_ascii_id'])) {
					$collection_ascii_id = $params['collection_ascii_id'];
				}
				if ($collection_ascii_id) {
					// instantiate collection and let this (singleton) hold it
					$dase->collection = Dase_Collection::get($collection_ascii_id);
				} else {
					$dase->collection= null;
				}

				//AUTHORIZATION:
				if (!isset($params['eid'])) {
					$params['eid'] = '';
				}
				if (isset($conf_array['auth']) && $conf_array['auth']) {
					Dase::checkUser($conf_array['auth'],$collection_ascii_id,$params['eid']);
				} else {
					//default auth is user!!!!!!!!!!!!!
					Dase::checkUser('user');
				}

				if (!isset($conf_array['mime'])) { 
					$conf_array['mime'] = 'text/html'; 
				}
				$dase->response_mime_type = $conf_array['mime'];
				$dase->params = $params;
				if ($module_prefix) {
					//modules, by convention, have one handler in a file named
					//'handler.php' with classname {Module}ModuleHandler
					include(DASE_PATH . $module_prefix . '/handler.php');
					$classname = ucfirst($conf_array['name']) . 'ModuleHandler';
				} else {
					include(DASE_PATH .  '/handlers/' . $conf_array['handler'] . '.php');
					$classname = ucfirst($conf_array['handler']) . 'Handler';
				}
				if(method_exists($classname,$conf_array['action'])) {
					//check cache, but only for 'get' method
					//NOTE that using the cache means you use the mime
					//type specified in 'routes.xml', so to set mime at
					//runtime, nocache="yes" should be set in routes.xml 
					//for the particular route
					//use nocache="custom" to document custom cache in
					//action file (here same as using 'yes')
					if ('get' == $method && !isset($conf_array['nocache'])) {
						$cache = new Dase_FileCache();
						$page = $cache->get();
						if ($page) {
							Dase::display($page,false);
							exit;
						} 
					}
					$msg = Dase::filterGet('msg');
					call_user_func(array($classname,$conf_array['action']));
					exit;
				} else { 
					//matched regex, but didn't find action
					Dase::log('error',"no handler for $request_url ($method)");
					Dase::error(500);
				}
			} 
		} 
		//no routes match, so use default:
		//having this "outlet" here guarantees only first match gets tested
		Dase::log('error',"$request_url could not be located");
		Dase::error(404);
		exit;
	}

	public static function error($code) {
		$msg = "check server error log for details";
		if (400 == $code) {
			header("HTTP/1.1 400 Bad Request");
		}
		if (404 == $code) {
			header("HTTP/1.1 404 Not Found");
		}
		if (401 == $code) {
			header('HTTP/1.1 401 Unauthorized');
			$msg = 'Unauthorized';
		}
		if (500 == $code) {
			header('HTTP/1.1 500 Internal Server Error');
		}
		Dase::display("Error: $msg",false);
		exit;
	}

	public static function display($content,$set_cache=true,$mime='') {
		if ($set_cache) {
			$cache = new Dase_FileCache();
			$cache->set($content);
		}
		$mime = $mime ? $mime : Dase::instance()->response_mime_type;

		//note: firefox gives me all sorts of trouble when I send
		//application/xhtml+xml.  this is a well-documented problem:
		//http://groups.google.com/group/habari-dev/msg/91d736688ee445ad

		header("Content-Type: $mime; charset=utf-8");
		echo $content;
		exit;
	}

	public static function redirect($path='',$msg='',$code="303") {
		//SHOULD use 303 (redirect after put,post,delte)
		//OR 307 -- no go -- look here
		$msg_qstring = '';
		$msg = urlencode($msg);
		if ($msg) {
			$msg_qstring = "?msg=$msg";
		}
		//NOTE that this redirect may be innapropriate when
		//client expect something OTHER than html (e.g., json,text,xml)
		header( "Location:". trim(APP_ROOT,'/') . "/" . trim($path,'/') . $msg_qstring,TRUE,$code);
		exit;
	}
}
