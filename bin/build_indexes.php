<?php
define('DASE_PATH', '..');

ini_set('include_path',DASE_PATH.'/lib');


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

if (isset($_SERVER['HTTP_HOST'])) {
	$protocol = (!isset($_SERVER['HTTPS'])) ? 'http://' : 'https://'; 
	define('APP_ROOT',trim($protocol.$_SERVER['HTTP_HOST'].'/'.trim(dirname($_SERVER['SCRIPT_NAME']),'/'),'/'));
	define('APP_HTTP_ROOT',str_replace('https:','http:',APP_ROOT));
	define('APP_HTTPS_ROOT',str_replace('http:','https:',APP_ROOT));
	//when dase is not a the server root
	define('APP_BASE',trim(dirname($_SERVER['SCRIPT_NAME']),'/'));
}

function __autoload($class_name) {
	$class_name = preg_replace('/_/','/',$class_name);
	$class_file = $class_name . '.php';
	try {
		include "$class_file";
	} catch (Exception $e) {
		print("could not autoload $class_file");
	}
}

Dase_Timer::start();
$coll = new Dase_DBO_Collection;
foreach ($coll->find() as $c) {
		print "working on " . $c->collection_name . "\n";
		$c->buildSearchIndex();
		print (Dase_Timer::getElapsed() . " seconds\n");
}
