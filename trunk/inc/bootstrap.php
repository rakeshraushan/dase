<?php
ini_set('include_path','lib');

//PHP ERROR REPORTING
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
//when dase is not a the server root
define('APP_BASE',trim(dirname($_SERVER['SCRIPT_NAME']),'/'));
define('CACHE_DIR', DASE_PATH . '/cache/');
define('DASE_CONFIG', DASE_PATH . '/inc/config.php');
define('CACHE_TTL',10);

define('DASE_LOG', DASE_PATH . '/log/dase.log');

define('LOG_LEVEL',3);

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
