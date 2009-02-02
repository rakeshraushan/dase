<?php
define("APP_ROOT","http://dev.laits.utexas.edu/itsprop/new");
define("BIN_PATH", dirname(__FILE__));
define("DASE_PATH",BIN_PATH . '/../../..');
define('CACHE_DIR', DASE_PATH . '/cache/');
define("DASE_CONFIG", DASE_PATH . '/inc/config.php');

define('DASE_LOG', DASE_PATH . '/var/log/dase.log');
define('LOG_LEVEL', 3);
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
