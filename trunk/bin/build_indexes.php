<?php

include 'laptop_cli.php';

Dase_Timer::start();
$coll = new Dase_DBO_Collection;
foreach ($coll->find() as $c) {
		print "working on " . $c->collection_name . "\n";
		$c->buildSearchIndex();
		print (Dase_Timer::getElapsed() . " seconds\n");
}
