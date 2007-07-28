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

	public function checkUser() {
		$controller = Dase::instance();
		if ('api' == $controller->module) {
			Dase::basicHttpAuth();
		} else {
			//by convention, plugins needing to bypass user check 
			//should include word 'login'  or 'public' in action
			if (!preg_match('/(login|public)/',$controller->action)) {
				if (empty( self::$user)) {
					self::$user = new Dase_User();
				}
			}
		}
	}

	public static function getUser() {
		if (self::$user) {
			return self::$user;
		} else {
			self::$user = new Dase_User();
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

	static public function run() {
		$controller = Dase::instance();
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
		//get routes


//come up w/ way for plugin to hook into routes here!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!



		include (DASE_CONFIG); 
		foreach ($routes as $route => $regex_array) {
			foreach ($regex_array as $regex) {
				if (preg_match("!$regex!",$request_url,$matches)) {
					if(file_exists(DASE_PATH . '/actions/' . $route . '.php')) {
						if ($matches[1]) {
							array_shift($matches);
							$params = $matches;
						}
						include(DASE_PATH . '/actions/' . $route . '.php');
						exit;
					}
				} 
			}
		}
		//no routes match, so use default:
		include(DASE_PATH . '/actions/list_collections.php');
		exit;
	}

	static public function listCollections() {
		Dase::checkUser();
		$coll = new Dase_DB_Collection;
		$tpl = Dase_Template::instance();
		$coll->orderBy('collection_name');
		$collections = $coll->getAll();
		$current_collections = Dase::getCurrentCollections();
		if (!$current_collections) {
			foreach ($collections as $coll) {
				$current_collections[] = $coll['id'];
			}
		}
		$tpl->assign('last_search',Dase_Session::get('last_search'));
		$tpl->assign('current_collections',$current_collections);
		$tpl->assign('collections',$collections);
		$tpl->assign('content','collections');
		Dase_Plugins::act('dase','before_display');
		$tpl->display('page.tpl');
	}

	static public function browseCollection() {
		echo "greetings from dase\n";
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
