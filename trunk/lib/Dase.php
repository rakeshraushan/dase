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
		if ($request->module) {
			//modules, by convention, have one handler in a file named
			//'handler.php' with classname {Module}ModuleHandler
			$handler_file = DASE_PATH.'/modules/'.$request->module.'/handler.php';
			$classname = ucfirst($request->module) . 'ModuleHandler';
		} else {
			include(DASE_PATH.'/handlers/'.$request->handler.'.php');
			$classname = ucfirst($request->handler).'Handler';
		}
		if (class_exists($classname,false)) {
			$handler = new $classname;
			$handler->dispatch($request);
		} else {
			Dase::error(404);
		}
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
		Dase_Log::info('redirecting to '.$redirect_path);
		header("Location:". $redirect_path,TRUE,$code);
		exit;
	}

	public static function error($code,$msg='')
	{
		switch ($code) {
		case 400:
			header("HTTP/1.1 400 Bad Request");
			$msg = 'Bad Request';
		case 404:
			header("HTTP/1.1 404 Not Found");
			$msg = '404 not found';
		case 401:
			header('HTTP/1.1 401 Unauthorized');
			$msg = 'Unauthorized';
		case 500:
			header('HTTP/1.1 500 Internal Server Error');
		case 411:
			header("HTTP/1.1 411 Length Required");
		case 415:
			header("HTTP/1.1 415 Unsupported Media Type");
		}

		if (defined('DEBUG')) {
			$request = new Dase_Http_Request;
			header("Content-Type: text/plain; charset=utf-8");
			print "================================\n";
			print "[http_error_code] => $code\n";
			print $request;
			print "================================\n";
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
}
