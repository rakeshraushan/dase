<?php
/*
 * Copyright 2008 The University of Texas at Austin
 *
 * This file is part of DASe.
 * 
 * DASe is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * DASe is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with DASe.  If not, see <http://www.gnu.org/licenses/>.
 */ 

class Dase 
{

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
					$clean_matches = Dase_Filter::filterArray($matches);
					//match param value to its param key
					if (count($params) == count($clean_matches)) {
						$params = array_combine($params,$clean_matches);
					} else {
						print_r($params);
						print_r($clean_matches);
						die ("routes error");
					}
				}
				if (Dase_Filter::filterGet('debug_route')) {
					//use this to make a handy debug bookmarklet!
					echo "$regex => {$conf_array['action']}";
					exit;
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
				if (!Dase_Auth::authorize($conf_array['auth'],$params)) {
					if ('text/html' == Dase_Registry::get('response_mime_type')) {
						//guarantees cookies will be deleted:
						Dase::redirect('logoff');
					} else {
						Dase::error(401);
					}
				} else {
					//good to go
				}

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
						$cache = Dase_Cache::get();
						$page = $cache->getData();
						if ($page) {
							if (defined('DEBUG')) {
								Dase::log('standard','using cached page '.$cache->getLoc());
							}
							Dase::display($page,false);
							exit;
						} 
					}
					$msg = Dase_Filter::filterGet('msg');
					if (defined('DEBUG')) {
						Dase::log('standard',"calling method {$conf_array['action']} on class $classname");
					}
					//call the action on the handler
					Dase_Registry::set('handler',$conf_array['handler']);
					Dase_Registry::set('action',$conf_array['action']);
					//passes $params into static method
					call_user_func(array($classname,$conf_array['action']),$params);
					exit;
				} else { 
					//matched regex, but didn't find action
					Dase::log('error',"no handler for $request_url ($method)");
					Dase::error(500);
				}
			} 
		} 
		//no routes match, so use default:
		Dase::log('error',"$request_url could not be located");
		Dase::error(404);
		exit;
	}

	public static function display($content,$set_cache=true)
	{
		if ($set_cache) {
			$cache = Dase_Cache::get();
			$cache->setData($content);
		}
		$mime = Dase_Registry::get('response_mime_type');
		header("Content-Type: $mime; charset=utf-8");
		echo $content;
		exit;
	}

	public static function redirect($path='',$msg='',$code="303")
	{
		//SHOULD use 303 (redirect after put,post,delete)
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

	public static function error($code)
	{
		$msg = "";
		if (400 == $code) {
			header("HTTP/1.1 400 Bad Request");
			$msg = 'Bad Request';
		}
		if (404 == $code) {
			header("HTTP/1.1 404 Not Found");
			$msg = '404 not found';
		}
		if (401 == $code) {
			header('HTTP/1.1 401 Unauthorized');
			$msg = 'Unauthorized';
		}
		if (500 == $code) {
			header('HTTP/1.1 500 Internal Server Error');
		}
		if (411 == $code) {
			header("HTTP/1.1 411 Length Required");
		}
		if (415 == $code) {
			header("HTTP/1.1 415 Unsupported Media Type");
		}

		if (defined('DEBUG')) {
			header("Content-Type: text/plain; charset=utf-8");
			print "Registry Array:\n";
			print "================================\n";
			print "[http_error_code] => $code\n";
			foreach (Dase_Registry::dump() as $k => $v) {
				print "[$k] => $v\n";
			}
			print "\n";
			print "Routes Array:\n";
			print "================================\n";
			foreach (Dase_Routes::compile() as $method => $routes) {
				foreach ($routes as $regex => $atts) {
					if (isset($atts['handler'])) {
						print "$method: [$regex] => {$atts['handler']}::{$atts['action']}\n";
					} else {
						print "$method: [$regex] => handler::{$atts['action']}\n";
					}
				}
			}
		} else {
			$t = new Dase_Xslt;
			$t->stylesheet = XSLT_PATH.'error/production.xsl';
			$t->set('msg',$msg);
			echo $t->transform();
		}
		exit;
	}

	public static function getConfig($key)
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
		$msg = $date.'| pid:'.getmypid().':'.$msg."\n";
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
}
