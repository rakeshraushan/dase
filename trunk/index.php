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

define('DASE_PATH', dirname(__FILE__));

ini_set('include_path','lib');

//PHP ERROR REPORTING
ini_set('display_errors',1);
ini_set('log_errors',1);
ini_set('error_log',DASE_PATH . '/log/error.log');
error_reporting(E_ALL);

//scripts using Dase library can set this
define('DASE_CONFIG', DASE_PATH . '/inc/config.php');

//need to be writable by apache:
define('CACHE_DIR', DASE_PATH . '/cache/');
define('DASE_LOG', DASE_PATH . '/log/dase.log');

define('LOG_LEVEL',3);

$protocol = (!isset($_SERVER['HTTPS'])) ? 'http://' : 'https://'; 
define('APP_ROOT',trim($protocol.$_SERVER['HTTP_HOST'].'/'.trim(dirname($_SERVER['SCRIPT_NAME']),'/'),'/'));
define('APP_HTTP_ROOT',str_replace('https:','http:',APP_ROOT));
define('APP_HTTPS_ROOT',str_replace('http:','https:',APP_ROOT));
//when dase is not a the server root
define('APP_BASE',trim(dirname($_SERVER['SCRIPT_NAME']),'/'));

function __autoload($class_name) {
	$include_path_tokens = explode(':', get_include_path());
	foreach($include_path_tokens as $prefix){
		$class_file = DASE_PATH.'/'.$prefix . '/' . preg_replace('/_/','/',$class_name) . '.php';
		if(file_exists($class_file)){
			require_once $class_file;
			return;
		}
	}  
	Dase_Log::info("could not autoload $class_file");
}
Dase_Timer::start();
Dase_Log::start();
Dase::run();
