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
		$request = new Dase_Http_Request;
		$routes = Dase_Routes::compile();

		//dispatch table is filtered by method & format
		if (!isset($routes[$request->method][$request->format])) {
			Dase::log('error','missing route for '.$request->method.' '.$request->format);
			Dase::error(500);
		}
		foreach ($routes[$request->method][$request->format] as $regex => $conf_array) {
			$matches = array();
			if (preg_match("!$regex!",$request->path,$matches)) {
				//if debug in force, log action
				if (defined('DEBUG')) {
					Dase::log('standard','--------- beginning DASe route -------------');
					Dase::log('standard',$regex . " => " . $conf_array['action']);
					Dase::log('standard',"path => " . $request->path);
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
					//match param value to its param key
					//this is safe, since we are matching '\w'
					if (count($params) == count($matches)) {
						$params = array_combine($params,$matches);
					} else {
						print_r($params);
						print_r($matches);
						die ("routes error");
					}
					$request->setParams($params);
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
					header("Location:".APP_ROOT.'/logoff',TRUE,401);
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
					if ('get' == $request->method) {
						$cache = Dase_Cache::get($request);
						if ($cache->isFresh()) {
							if (defined('DEBUG')) {
								Dase::log('standard','using cached page '.$cache->getLoc());
							}
							$cache->display(); //this method exits
						} 
					}
					if (defined('DEBUG')) {
						Dase::log('standard',"calling method {$conf_array['action']} on class $classname");
					}

					$request->set('handler',$conf_array['handler']);
					$request->set('action',$conf_array['action']);

					//call static method, passing in request obj
					call_user_func(array($classname,$conf_array['action']),$request);
					exit;
				} else { 
					//matched regex, but didn't find action
					Dase::log('error',"no handler for $request->path ($method)");
					Dase::error(500);
				}
			} 
		} 
		//no routes match, so use default:
		Dase::log('error',"$request->path could not be located");
		Dase::error(404);
		exit;
	}

	public static function display($content,$request,$set_cache=true)
	{
		if ($set_cache) {
			$cache = Dase_Cache::get($request);
			$cache->setData($content);
		}
		header("Content-Type: ".$request->response_mime_type."; charset=utf-8");
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

	public static function error($code,$msg='')
	{
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
			print "\n";
			print "Routes Array:\n";
			print "================================\n";
			foreach (Dase_Routes::compile() as $method => $formats) {
				foreach ($formats as $format => $routes) {
				foreach ($routes as $regex => $atts) {
					if (isset($atts['handler'])) {
						print "($format) $method: [$regex] => {$atts['handler']}::{$atts['action']}\n";
					} else {
						print "($format) $method: [$regex] => handler::{$atts['action']}\n";
					}
				}
				}
			}
		} else {
			//todo: pretty error message for production
			header("Content-Type: text/plain; charset=utf-8");
			print "DASe Error Report\n\n";
			print "[http_error_code] => $code\n";
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
