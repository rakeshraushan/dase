<?php
define("APP_ROOT","http://dase.laits.utexas.edu");
define("DASE_URL","http://dase.laits.utexas.edu");
define("BIN_PATH", dirname(__FILE__));
define("DEBUG",1);
define("DASE_PATH",BIN_PATH . '/..');
define('CACHE_DIR', DASE_PATH . '/var/cache/');
define("DASE_CONFIG", DASE_PATH . '/../daseconf.php');
define('CART',1);
define('USER_COLLECTION',2);
define('SLIDESHOW',3);
define('CACHE_TTL',10);

define('DASE_LOG', DASE_PATH . '/var/log/dase.log');
define('DASE_LOG_INFO',1);
define('DASE_LOG_DEBUG',2);
define('DASE_LOG_ALL',3);

define("LOG_LEVEL",DASE_LOG_DEBUG);
ini_set('include_path',ini_get('include_path').':'. DASE_PATH .'/lib:'); 

function __autoload($class_name) {
	$class_name = preg_replace('/_/','/',$class_name);
	$class_file = $class_name . '.php';
	try {
		include "$class_file";
	} catch (Exception $e) {
		print("could not autoload $class_file");
	}
}

if (isset($database)) {
	Dase_DB::get($database);
}

