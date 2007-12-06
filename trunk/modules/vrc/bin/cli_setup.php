<?php
define("DASE_PATH",'/mnt/home/pkeane/sdase');
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

$host = "SQL01.austin.utexas.edu:1036";
$name = "vrc_live";
$user = "dasevrc";
$pass = "d453vrc";
