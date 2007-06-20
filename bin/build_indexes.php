<?php
define('DASE_PATH', '..');
define('CLI_BATCH',1);
define('DEBUG',1);
ini_set('include_path','/usr/local/dase/lib:/usr/local/dase/lib/Smarty:.:../lib');

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
$coll = new Dase_DB_Collection;
foreach ($coll->getAll() as $c) {
	print "working on " . $c->collection_name . "\n";
	$c->buildSearchIndex();
	print (Dase_Timer::getElapsed() . " seconds\n");
}
