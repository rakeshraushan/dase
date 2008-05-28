<?php
ini_set('include_path','.:lib');

//PHP ERROR REPORTING
ini_set('display_errors','on');
ini_set('log_errors','on');
ini_set('error_log',DASE_PATH . '/var/log/error.log');
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
define('CACHE_DIR', DASE_PATH . '/var/cache/');
define('SCHEMA_DIR', DASE_PATH . '/var/schema/');
define('DASE_CONFIG', DASE_PATH . '/inc/config.php');
define('MAX_ITEMS',30);
define('CACHE_TTL',10);

define('DASE_LOG', DASE_PATH . '/var/log/dase.log');
define('DASE_LOG_INFO',1);
define('DASE_LOG_DEBUG',2);
define('DASE_LOG_ALL',3);

define('LOG_LEVEL',DASE_LOG_DEBUG);

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
