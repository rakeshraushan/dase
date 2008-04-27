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

ini_set('include_path','.:lib');
define('DASE_PATH', dirname(__FILE__));
ini_set('display_errors','on');
ini_set('log_errors','on');
ini_set('error_log',DASE_PATH . '/log/error.log');
//error_reporting(E_STRICT);
error_reporting(E_ALL);

if (isset($_SERVER['HTTPS'])) {
	$protocol = "https://";
} else {
	$protocol = "http://";
}

define('DEBUG',1); 
define('APP_ROOT',trim($protocol . $_SERVER['HTTP_HOST'] . '/' . trim(dirname($_SERVER['SCRIPT_NAME']),'/'),'/'));
define('APP_HTTP_ROOT','http://' . $_SERVER['HTTP_HOST'] . '/' . trim(dirname($_SERVER['SCRIPT_NAME']),'/'));
define('APP_HTTPS_ROOT','https://' . $_SERVER['HTTP_HOST'] . '/' . trim(dirname($_SERVER['SCRIPT_NAME']),'/'));
define('APP_BASE',trim(dirname($_SERVER['SCRIPT_NAME']),'/'));
define('CACHE_DIR', DASE_PATH . '/cache/');
define('LOG_DIR', DASE_PATH . '/log/');
define('DASE_CONFIG', DASE_PATH . '/inc/config.php');
define('XSLT_PATH', DASE_PATH . '/xslt/');
define('MAX_ITEMS',30);

//for dase 'client' code
define('DASE_URL',APP_ROOT);

//???????
define('CART',1);
define('USER_COLLECTION',2);
define('SLIDESHOW',3);

function __autoload($class_name) {
	$class_file = preg_replace('/_/','/',$class_name) . '.php';
	try {
		include "$class_file";
	} catch (Exception $e) {
		Dase::log('error',"could not autoload $class_file");
	}
}

Dase_Timer::start();
Dase::run();
