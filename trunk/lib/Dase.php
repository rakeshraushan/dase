<?php

class Dase 
{
	private static $instance;
	public static $user;
	public $base_url= '';       
	private $stub= '';           
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

	public function checkUser() {
		$controller = Dase::instance();
		//by convention, plugins needing to bypass user check 
		//should include word 'login'  or 'public' in action
		if (!preg_match('/(login|public)/',$controller->action)
			) {
			if (empty( self::$user)) {
				self::$user = new Dase_User();
			}
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

	static public function parseRequest() {
		$controller = Dase::instance();
		// from habari code
		// Start with the entire URL coming from web server... 
		$start_url= ( isset($_SERVER['REQUEST_URI']) 
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
			$start_url= str_replace(APP_BASE,'',$start_url);
		}

		/* Trim off any leading or trailing slashes */
		$start_url= trim($start_url, '/');

		/* Remove the querystring from the URL */
		if ( strpos($start_url, '?') !== FALSE )
			list($start_url, )= explode('?', $start_url);

		$controller->stub = $start_url;
		$stub_array = explode('/',$start_url);
		if (strstr($stub_array[0],'_collection')) {
			array_unshift($stub_array,'collection');
		}
		$module = Dase_Utils::camelCaseString(array_shift($stub_array));
		if (!$module) { 
			//this is the case of the app root being requested
			$module = 'collection'; 
			//never another action
			$stub_array = array();
		}
		$module = Dase_Plugins::filter('dase','module',$module);
		$handler = 'Dase_' . ucfirst($module) . 'Handler';
		if (class_exists($handler)) {
			Dase_Log::write('handler ok: ' . $handler );
			if (isset($stub_array[0])) {
				$action = Dase_Utils::camelCaseString($stub_array[0]);
			} else {
				$action = '';
			}
			Dase_Log::write('try action: ' . $action );
			if (method_exists( $handler,$action )) {
				array_shift($stub_array);
				$controller->handler = $handler; 
				$controller->action = $action; 
				$controller->url_params = $stub_array;
			} else {
				$controller->handler = $handler; 
				$controller->action = 'index'; 
				$controller->url_params = $stub_array;
			}
		} else {
			Dase_Log::error('no such class: ' . $handler );
			Dase::reload('error','Sorry, there was an error');
		}
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

	public function dispatchRequest() {
		$c = Dase::instance();
		//check again in case getUser changed one or the other
		if (method_exists( $c->handler,$c->action )) {
			Dase_Log::write('dispatch: ' . $c->handler . '->' . $c->action);
			call_user_func_array(array($c->handler,$c->action),$c->url_params); //retrieve $_GET and $_POST w/in action
		} else {
			Dase_Log::error('no such class->method: ' . $c->handler . '->' . $c->action);
			Dase::reload('error','Sorry, there was an error');
		}
	}
}
