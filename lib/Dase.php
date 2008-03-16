<?php

class Dase 
{

	//this is the "application" class which holds the 
	//static methods for the flow of the app

	public static function getConf($key)
	{
		$conf = array();
		include(DASE_CONFIG);
		if (isset($conf[$key])) {
			return $conf[$key];
		} else {
			throw new Exception("no such configuration key: $key");
		}
	}

	public static function log($logfile,$msg)
	{
		$date = date(DATE_W3C);
		$msg = "$date : $msg\n";
		if(file_exists(LOG_DIR . "{$logfile}.log")) {
			file_put_contents(LOG_DIR ."{$logfile}.log",$msg,FILE_APPEND);
		}
		if ('error' == $logfile) {
			//include backtrace w/ errors
			ob_start();
			debug_print_backtrace();
			$trace = ob_get_contents();
			ob_end_clean();
			file_put_contents(LOG_DIR ."error.log",$trace,FILE_APPEND);
		}
	}

	public static function filterArray($ar)
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

	public static function filterGetArray()
	{
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

	public static function filterGet($key)
	{
		if (Dase_Util::getVersion() >= 520) {
			return trim(filter_input(INPUT_GET, $key, FILTER_SANITIZE_STRING));
		} else {
			if (isset($_GET[$key])) {
				return trim(strip_tags($_GET[$key]));
			}
		}
	}

	public static function filterPost($key)
	{
		if (Dase_Util::getVersion() >= 520) {
			return trim(filter_input(INPUT_POST, $key, FILTER_SANITIZE_STRING));
		} else {
			if (isset($_POST[$key])) {
				return strip_tags($_POST[$key]);
			}
		}
	}

	public static function run()
	{

		$request_url = Dase_Url::getRequestUrl(); 
		Dase_Registry::set('request_url',$request_url);
		$routes = Dase_Routes::compile();

		//note: there is only ONE method on a request
		//so that is the only route map we need to traverse
		$method = strtolower($_SERVER['REQUEST_METHOD']);
		Dase_Registry::set('method',$method);
		//look through dispatch table for match
		foreach ($routes[$method] as $regex => $conf_array) {
			$matches = array();
			if (preg_match("!$regex!",$request_url,$matches)) {
				//if debug in force, log action
				if (defined('DEBUG')) {
					Dase::log('standard','--------- beginning DASe route -------------');
					Dase::log('standard',$regex . " => " . $conf_array['action']);
					Dase::log('standard',"request_url => " . $request_url);
				}
				$params = array();
				if (isset($conf_array['params'])) {
					$params = explode('/',$conf_array['params']);
					Dase::log('standard',"params => " . $conf_array['params']);
				}
				$module_prefix = '';
				//if prefix is set, it means this is a module request
				if (isset($conf_array['prefix'])) {
					$module_prefix = $conf_array['prefix'];
					define('MODULE_PATH',DASE_PATH . $module_prefix);
					define('MODULE_ROOT',APP_ROOT . $module_prefix);
					//modules can include class files in 'lib' dir
					ini_set('include_path',ini_get('include_path').':'.MODULE_PATH.'/lib');
				}
				if (isset($matches[1])) { // i.e. at least one paramenter
					//don't need matches[0] (see preg_match docs)
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

				//if collection_ascii_id is set, go ahead and instantiate collection!

				if (isset($params['collection_ascii_id']) && $params['collection_ascii_id']) {
					$collection_ascii_id = $params['collection_ascii_id'];
					Dase_Registry::set('collection',Dase_DBO_Collection::get($collection_ascii_id));
				}

				if (isset($conf_array['mime'])) {
					//note: firefox gives me all sorts of trouble when I send
					//application/xhtml+xml.  this is a well-documented problem:
					//http://groups.google.com/group/habari-dev/msg/91d736688ee445ad
					Dase_Registry::set('response_mime_type',$conf_array['mime']);
				} else {
					Dase_Registry::set('response_mime_type','text/html');
				}	

				//AUTHORIZATION:
				if (!isset($params['eid'])) {
					$params['eid'] = '';
				}
				if (!isset($conf_array['auth']) || !$conf_array['auth']) {
					//default required auth is 'user' (i.e., ANY valid user
					$conf_array['auth'] = 'user';
				}

				//a simple authorization check roadblock
				if (!Dase_Auth::authorize($conf_array['auth'],$collection_ascii_id,$params['eid'])) {
					if ('text/html' == Dase_Registry::get('response_mime_type')) {
						//guarantees cookies will be deleted:
						Dase::redirect('logoff');
					} else {
						Dase_Error::report(401);
					}
				} else {
					//good to go
				}

				Dase_Registry::set('params',$params);
				if ($module_prefix) {
					//modules, by convention, have one handler in a file named
					//'handler.php' with classname {Module}ModuleHandler
					include(DASE_PATH . $module_prefix . '/handler.php');
					$conf_array['handler'] = $conf_array['name'] . '_module'; // so we can set this->handler 
					$classname = ucfirst($conf_array['name']) . 'ModuleHandler';
				} else {
					include(DASE_PATH .  '/handlers/' . $conf_array['handler'] . '.php');
					$classname = ucfirst($conf_array['handler']) . 'Handler';
				}
				if(method_exists($classname,$conf_array['action'])) {
					//check cache, but only for 'get' method
					//NOTE that using the cache means you use the mime
					//type specified in routes config, so to set mime at
					//runtime, nocache="yes" should be set in routes config
					//for the particular route
					//use nocache="custom" to document custom cache in
					//action file (here same as using 'yes')
					if ('get' == $method && !isset($conf_array['nocache'])) {
						$cache = new Dase_Cache();
						$page = $cache->get();
						if ($page) {
							if (defined('DEBUG')) {
								Dase::log('standard','------- using cache -------');
								Dase::log('standard','using cached page '.$page);
								Dase::log('standard','---------------------------');
							}
							Dase::display($page,false);
							exit;
						} 
					}
					$msg = Dase::filterGet('msg');
					if (defined('DEBUG')) {
						Dase::log('standard','------ call_user_func -----');
						Dase::log('standard',"calling method {$conf_array['action']} on class $classname");
						Dase::log('standard','---------------------------');
					}
					//call the action on the handler
					Dase_Registry::set('handler',$conf_array['handler']);
					Dase_Registry::set('action',$conf_array['action']);
					call_user_func(array($classname,$conf_array['action']));
					exit;
				} else { 
					//matched regex, but didn't find action
					Dase::log('error',"no handler for $request_url ($method)");
					Dase_Error::report(500);
				}
			} 
		} 
		//no routes match, so use default:
		//having this "outlet" here guarantees only first match gets tested
		Dase::log('error',"$request_url could not be located");
		Dase_Error::report(404);
		exit;
	}

	public static function display($content,$set_cache=true)
	{
		if ($set_cache) {
			$cache = new Dase_Cache();
			$cache->set($content);
		}
		$mime = Dase_Registry::get('response_mime_type');
		header("Content-Type: $mime; charset=utf-8");
		echo $content;
		exit;
	}

	public static function redirect($path='',$msg='',$code="303")
	{
		//SHOULD use 303 (redirect after put,post,delte)
		//OR 307 -- no go -- look here
		$msg_qstring = '';
		$msg = urlencode($msg);
		if ($msg) {
			$msg_qstring = "?msg=$msg";
		}
		//NOTE that this redirect may be innapropriate when
		//client expect something OTHER than html (e.g., json,text,xml)
		$redirect_path = trim(APP_ROOT,'/') . "/" . trim($path,'/') . $msg_qstring;
		if (defined('DEBUG')) {
			Dase::log('standard','redirecting to '.$redirect_path);
		}
		header("Location:". $redirect_path,TRUE,$code);
		exit;
	}
}
