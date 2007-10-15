<?php

class Dase 
{
	private static $instance;
	public static $user;
	public $base_url= '';       
	public $collection;
	private $module= '';        
	private $url_params = array();    

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
			throw new Exception('no such configuration key');
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
			if (('dase' == $_SERVER['PHP_AUTH_USER']) && ('api' == $_SERVER['PHP_AUTH_PW'])) {
				return;
			}
		}
		header('WWW-Authenticate: Basic realm="DASe"');
		header('HTTP/1.1 401 Unauthorized');
		echo "please enter a valid username and password";
		exit;
	}

	public function checkUser($auth = 'user',$collection_ascii_id = '',$eid = '') {
		switch ($auth) {
		case 'user':
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
			self::$user = new Dase_User();
			if (!in_array(self::$user->eid,Dase::getConf('superuser'))) {
				Dase::error(401);
			}
			break;
		case 'admin':
			self::$user = new Dase_User();
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
			Dase::basicHttpAuth();
			break;
		case 'token':
			if (!in_array(Dase::filterGet('token'),Dase::getConf('token'))) {
				Dase::error(401);
			}
			break;
		case 'eid':
			self::$user = new Dase_User();
			if (self::$user->eid != $eid) {
				Dase::error(401);
			}
			break;
		case 'none':
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
		if (Dase_Utils::getVersion() >= 520) {
			return filter_var_array($ar, FILTER_SANITIZE_STRING);
		} else {
			foreach ($ar as $k => $v) {
				$ar[$k] = strip_tags($v);
			}
			return $ar;
		}
	}

	public static function filterGetArray() {
		if (Dase_Utils::getVersion() >= 520) {
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
		if (Dase_Utils::getVersion() >= 520) {
			return trim(filter_input(INPUT_GET, $key, FILTER_SANITIZE_STRING));
		} else {
			if (isset($_GET[$key])) {
				return trim(strip_tags($_GET[$key]));
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

	static public function compileRoutes() {
		return Dase::_compileModuleRoutes(Dase::_compileRoutes());
	}

	static private function _compileRoutes($prefix = '',$collection = null,$routes = null) {
		$params = '';
		$path = DASE_PATH . $prefix . '/inc/routes.xml';
		$sx = simplexml_load_file($path);
		foreach ($sx->route as $route) {
			if (!$route->match) {
				//THESE ARE SINGLE LINE (NO MATCH) ROUTES
				$regex = (string) $route['action'];
				if (isset($route['method'])) {
					$method = (string) $route['method'];
				} else {
					$method = 'get';
				}
				if ($prefix) {
					$regex = trim($prefix,'/') . '/' . $regex;
				}
				if (isset($route['params'])) {
					$params = (string) $route['params'];
					foreach (explode('/',$params) as $p) {
						$regex .= "/([^/]*)";
					}
				}
				$regex = '^' . $regex . '$';
				if ($params) {
					$routes[$method][$regex]['params'] = $params;
				}
				$routes[$method][$regex]['action'] = (string) $route['action'];
				if (isset($route['auth'])) {
					$routes[$method][$regex]['auth'] = (string) $route['auth'];
				}
				if ($prefix) {
					$routes[$method][$regex]['prefix'] = $prefix;
				}
			}
			foreach ($route->match as $match) {
				$regex = (string) $match;
				if (isset($match['method'])) {
					$method = (string) $match['method'];
				} elseif (isset($route['method'])) {
					$method = (string) $route['method'];
				} else {
					$method = 'get';
				}	
				if ($prefix) {
					$regex = trim($prefix,'/') . '/' . $regex;
					$regex = trim($regex,'/'); //in case there had been no original regex
				}
				if (isset($match['params'])) {
					$params = (string) $match['params'];
				} else {
					$params = (string) $route['params'];
				}
				if ($params) {
					foreach (explode('/',$params) as $p) {
						$regex .= "/([^/]*)";
					}
				}
				$regex = '^' . $regex . '$';
				if ($params) {
					$routes[$method][$regex]['params'] = $params;
				}
				$routes[$method][$regex]['action'] = (string) $route['action'];
				if (isset($match['caps'])) {
					$routes[$method][$regex]['caps'] = (string) $match['caps'];
				}
				if (isset($match['auth'])) {
					$routes[$method][$regex]['auth'] = (string) $match['auth'];
				} else {
					$routes[$method][$regex]['auth'] = (string) $route['auth'];
				}
				if ($prefix) {
					$routes[$method][$regex]['prefix'] = $prefix;
				}
				if ($collection) {
					$routes[$method][$regex]['collection'] = $collection;
				}
			}
		}
		return $routes;
	}

	static private function _compileModuleRoutes($routes) {
		include(DASE_CONFIG);
		$dir = (DASE_PATH . "/modules");
		foreach (new DirectoryIterator($dir) as $file) {
			if ($file->isDir() && !$file->isDot()) {
				$module = $file->getFilename();
				if (
					is_file("$dir/$module/inc/routes.xml") &&
					//note that module needs to be registered in DASE_CONFIG
					isset($conf['modules'][$module]) &&
					$conf['modules'][$module]
				) {
					$collection = $conf['modules'][$module];
					if (!is_string($collection)) {
						$collection = null;
					}
					$path = "$dir/$module/inc/routes.xml";
					$routes = Dase::_compileRoutes("/modules/$module",$collection,$routes);
				}
			}
		}
		return $routes;
	}

	static public function run() {
		$controller = Dase::instance();
		$routes = Dase::compileRoutes();
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

		/* Strip out the base URL from the requested URL */
		if ('/' != APP_BASE) {
			$request_url= str_replace(APP_BASE,'',$request_url);
		}

		/* Remove the querystring from the URL */
		if ( strpos($request_url, '?') !== FALSE ) {
			list($request_url, )= explode('?', $request_url);
		}

		/* Trim off any leading or trailing slashes */
		$request_url= trim($request_url, '/');

		$matches = array();
		$params = array();

		//note: there is only ONE method on a request
		//so that is the only route map we need to traverse
		$method = strtolower($_SERVER['REQUEST_METHOD']);

		foreach ($routes[$method] as $regex => $conf_array) {
			if (preg_match("!$regex!",$request_url,$matches)) {
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
				$action_prefix = '';
				if (isset($conf_array['prefix'])) {
					$action_prefix = $conf_array['prefix'];
				}
				if (isset($matches[1])) {
					array_shift($matches);
					$clean_matches = Dase::filterArray($matches);
					if (count($params) == count($clean_matches)) {
						$params = array_combine($params,$clean_matches);
					} else {
						print_r($params);
						print_r($clean_matches);
						die ("routes error");
					}
				}
				if (isset($conf_array['auth']) && $conf_array['auth']) {
					$eid = '';
					$collection_ascii_id = '';
					//conf_array['collection'] originates in configuration file config.php
					//modules can stipulate an associated collection
					//this is collection authorization for modules
					if (isset($conf_array['collection'])) {
						$collection_ascii_id = $conf_array['collection'];
						//params['collection_ascii_id'] originates in routes.xml
						//this is collection authorization for collection admin
					} elseif (isset($params['collection_ascii_id'])) {
						$collection_ascii_id = $params['collection_ascii_id'];
					} elseif (isset($params['eid'])) {
						$eid = $params['eid'];
						if (isset($params['collection_ascii_id'])) {
							$collection_ascii_id = $params['collection_ascii_id'];
						} else {
							$collection_ascii_id = '';
						}
					} else {
						$collection_ascii_id = '';
					}	
					if ($collection_ascii_id) {
						// instantiate collection and let this (singleton controller) hold it
						// note that here is a GOOD place to decide what kind (db,xml,remote)
						// of collection to get
						$controller->collection = Dase_Collection::get($collection_ascii_id,'db');
					}
					Dase::checkUser($conf_array['auth'],$collection_ascii_id,$eid);
				} else {
					//default auth is user!!!!!!!!!!!!!
					Dase::checkUser('user');
				}
				if(file_exists(DASE_PATH . $action_prefix . '/actions/' . $conf_array['action'] . '.php')) {
					if (Dase::filterGet('debug_route')) {
						//use this to make a handy debug bookmarklet!
						echo "$regex => {$conf_array['action']}";
						exit;
					}
					include(DASE_PATH . $action_prefix . '/actions/' . $conf_array['action'] . '.php');
					exit;
				} 
			}
		}
		//no routes match, so use default:
		Dase::error(404);
		exit;
	}

	public static function error($code, $msg = '') {
		$tpl = Dase_Template::instance();
		if (400 == $code) {
			header("HTTP/1.1 400 Bad Request");
			$tpl->assign('msg',$msg);
		}
		if (404 == $code) {
			header("HTTP/1.1 404 Not Found");
			$tpl->assign('msg','not found');
		}
		if (401 == $code) {
			header('HTTP/1.1 401 Unauthorized');
			$tpl->assign('msg','Unauthorized');
		}
		$tpl->assign('code',$code);
		$tpl->display('error/index.tpl');
		exit;
	}

	public static function reload($path = '',$msg = '') {
		$msg_qstring = '';
		$msg = urlencode($msg);
		if (!defined('NO_SESSIONS')) {
			Dase_Session::write();
		}
		if ($msg) {
			$msg_qstring = "?msg=$msg";
		}
		header( "Location:". trim(APP_ROOT,'/') . "/" . trim($path,'/') . $msg_qstring);
		exit;
	}
}
