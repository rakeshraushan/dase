<?php

$database = 'dase_prod';
include 'cli_setup.php';

$colls = array(
	'test_collection',
	'texpol_image_collection',
	'bsls_collection'
);

Dase_Timer::start();
$coll = new Dase_DB_Collection;
foreach ($coll->getAll() as $row) {
	if (in_array($row['ascii_id'],$colls)) {
		$c = new Dase_DB_Collection($row);
		print "working on " . $c->collection_name . "\n";
		$c->buildSearchIndex();
		print (Dase_Timer::getElapsed() . " seconds\n");
	}
}
