<?php

class Dase 
{
	private static $instance;
	public static $user;
	public $base_url= '';       
	private $stub= '';           
	private $module= '';        
	private $action= '';        
	private $handler= null;    
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
		header('HTTP/1.0 401 Unauthorized');
		echo "please enter a valid username and password";
		exit;
	}

	public function checkUser($auth = 'user') {
		switch ($auth) {
		case 'user':
			self::$user = new Dase_User();
			break;
		case 'manager':
			break;
		case 'superuser':
			break;
		case 'admin':
			self::$user = new Dase_User();
			if (!in_array(self::$user->eid,Dase::getConf('admin'))) {
				header('HTTP/1.0 401 Unauthorized');
				echo "unauthorized";
				exit;
			}
			break;
		case 'http':
			Dase::basicHttpAuth();
			break;
		case 'token':
			if (!in_array(Dase::filterGet('token'),Dase::getConf('token'))) {
				header('HTTP/1.0 401 Unauthorized');
				echo "unauthorized";
				exit;
			}
			break;
		case 'none':
			break;
		default:
			header("HTTP/1.0 404 Not Found");
			exit;
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

	static public function compileRoutes() {
		$sx = simplexml_load_file(DASE_PATH . '/inc/routes.xml');
		foreach ($sx->route as $route) {
			if (!$route->match) {
				$regex = "^" . (string) $route['name'];
				if (isset($route['params'])) {
					for ($i=0;$i<(int) $route['params'];$i++) {
						$regex .= "/([^/]*)";
					}
				}
				$regex .= "$";
				$conf[$regex]['action'] = (string) $route['name'];
				if (isset($route['auth'])) {
					$conf[$regex]['auth'] = (string) $route['auth'];
				}
			}
			foreach ($route->match as $match) {
				$regex = "^" . (string) $match;
				if (isset($match['params'])) {
					$params = (int) $match['params'];
				} else {
					$params = (int) $route['params'];
				}
				if ($params) {
					for ($i=0;$i<$params;$i++) {
						$regex .= "/([^/]*)";
					}
				}
				$regex .= "$";
				$conf[$regex]['action'] = (string) $route['name'];
				if (isset($match['auth'])) {
					$conf[$regex]['auth'] = (string) $match['auth'];
				} else {
					$conf[$regex]['auth'] = (string) $route['auth'];
				}
			}
		}
		return $conf;
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

		/* Trim off any leading or trailing slashes */
		$request_url= trim($request_url, '/');

		/* Remove the querystring from the URL */
		if ( strpos($request_url, '?') !== FALSE ) {
			list($request_url, )= explode('?', $request_url);
		}

		$matches = array();
		$params = array();

		//$controller->module = Dase_Plugins::filter('dase','request_url',$request_url);
		//come up w/ way for plugin to hook into routes here!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!

		include (DASE_CONFIG); 
		foreach ($routes as $regex => $conf_array) {
			if (preg_match("!$regex!",$request_url,$matches)) {
				if (isset($conf_array['auth']) && $conf_array['auth']) {
					Dase::checkUser($conf_array['auth']);
				}
				Dase::log('standard',$conf_array['action']);
				if(file_exists(DASE_PATH . '/actions/' . $conf_array['action'] . '.php')) {
					if (isset($matches[1])) {
						array_shift($matches);
						$params = $matches;
					}
					include(DASE_PATH . '/actions/' . $conf_array['action'] . '.php');
					exit;
				} 
			}
		}
		//no routes match, so use default:
		include(DASE_PATH . '/actions/list_collections.php');
		exit;
	}

	public static function reload($path = '',$msg = '') {
		$msg_qstring = '';
		$msg = urlencode($msg);
		Dase_Session::write();
		if ($msg) {
			$msg_qstring = "?msg=$msg";
		}
		header( "Location:". trim(APP_ROOT,'/') . "/" . trim($path,'/') . $msg_qstring);
		exit;
	}
}
