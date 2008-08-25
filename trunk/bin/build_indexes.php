<?php

include 'config.php';

//this script rebuilds search indexes

$coll_ascii_id = '';

$coll = new Dase_DBO_Collection;
if ($coll_ascii_id) {
	$coll->ascii_id = $coll_ascii_id;
}
foreach ($coll->find() as $c) {
	Dase_Timer::start(true);
	print "working on " . $c->collection_name . "\n";
	$c->buildSearchIndex();
	print (Dase_Timer::getElapsed() . " seconds\n");
}
