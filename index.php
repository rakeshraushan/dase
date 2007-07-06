<?php
ini_set('include_path','.:lib:plugins');

ini_set('display_errors',1);
error_reporting(E_ALL);

if (isset($_SERVER['HTTPS'])) {
	$protocol = "https://";
} else {
	$protocol = "http://";
}
define('DEBUG',1);
define('APP_ROOT',$protocol . $_SERVER['HTTP_HOST'] . '/' .trim(dirname($_SERVER['SCRIPT_NAME']),'/'));
define('APP_HTTP_ROOT','http://' . $_SERVER['HTTP_HOST'] . '/' . trim(dirname($_SERVER['SCRIPT_NAME']),'/'));
define('APP_HTTPS_ROOT','https://' . $_SERVER['HTTP_HOST'] . '/' . trim(dirname($_SERVER['SCRIPT_NAME']),'/'));
define('APP_BASE',trim(dirname($_SERVER['SCRIPT_NAME']),'/'));
define('DASE_PATH', dirname(__FILE__));
define('MEDIA_ROOT', '/usr/local/dase/media');

function __autoload($class_name) {
	$class_name = preg_replace('/_/','/',$class_name);
	if ('Smarty' != $class_name) {
		$class_file = $class_name . '.php';
	} else {
		$class_file = $class_name . '.class.php';
	}
	try {
		include "$class_file";
	} catch (Exception $e) {
		Dase_Log::error("could not autoload $class_file");
	}
}

Dase_Timer::start();
Dase_Session::read();
Dase_Plugins::load();

Dase::parseRequest();
Dase::checkUser();
Dase::dispatchRequest();
