<?php
define("BIN_PATH", dirname(__FILE__));
define("DASE_PATH",BIN_PATH . '/..');
define("DASE_CONFIG", DASE_PATH . '/../daseconf.php');
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

if (isset($messages)) {
	echo "using database -> "; 
	termcolored(strtoupper(Dase_DB::getDbName()));
	echo "\npress enter to continue or 'q' to quit\n";
	$char = fgetc(STDIN);
	if ('q' == $char) {
		exit;
	}
}