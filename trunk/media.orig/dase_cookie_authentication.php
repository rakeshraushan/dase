<?php

ini_set('display_errors',1);
error_reporting(E_ALL);
ini_set('include_path','../lib');
define('DASE_PATH', '..');
define('DASE_CONFIG','../inc/config.php');
include (DASE_CONFIG);

if (isset($_SERVER['HTTPS'])) {
	$protocol = "https://";
} else {
	$protocol = "http://";
}

//todo: fix this (harcoded path)!!!!!!!!!!!!!!!!!!!!!

define('APP_ROOT',$protocol . $_SERVER['HTTP_HOST'] . '/dase1');

function __autoload($class_name) {
	$class_file = preg_replace('/_/','/',$class_name) . '.php';
	try {
		include "$class_file";
	} catch (Exception $e) {
		Dase::log('error',"could not autoload $class_file");
	}
}

$eid = Dase_Cookie::getEid();
