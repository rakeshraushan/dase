<?php

$database = 'dase_prod';
include 'cli_setup.php';

Dase_Timer::start();
$coll = new Dase_DB_Collection;
foreach ($coll->getAll() as $row) {
	$c = new Dase_DB_Collection($row);
	print "working on " . $c->collection_name . "\n";
	$c->buildSearchIndex();
	print (Dase_Timer::getElapsed() . " seconds\n");
}
