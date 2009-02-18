<?php

define("APP_ROOT","http://quickdraw.laits.utexas.edu/dase1");
define("DASE_PATH",dirname(__FILE__).'/..');
define("DASE_CONFIG", DASE_PATH . '/inc/config.php');
define("DASE_LOCAL_CONFIG", DASE_PATH . '/inc/local_config.php');
ini_set('include_path',ini_get('include_path').':'. DASE_PATH .'/lib:'); 

function __autoload($class_name) {
	$include_path_tokens = explode(':', get_include_path());
	foreach($include_path_tokens as $prefix){
		$class_file = DASE_PATH.'/'.$prefix . '/' . preg_replace('/_/','/',$class_name) . '.php';
		if(file_exists($class_file)){
			require_once $class_file;
			return;
		}
	}  
}
