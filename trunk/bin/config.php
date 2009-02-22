<?php
ini_set('include_path','../lib');
function __autoload($class_name) {
	@include __autoloadFilename($class_name);
}
function __autoloadFilename($class_name) {
	return str_replace('_','/',$class_name) . '.php';
}

$config = new Dase_Config(dirname(__FILE__).'/..');
$config->load('inc/config.php');
$config->load('inc/local_config.php');
$log = new Dase_Log($config->getLogDir(),'dase.log',Dase_Log::OFF);
$db = new Dase_DB($config->get('db'),$log);

