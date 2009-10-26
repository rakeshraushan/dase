<?php

include 'config.php';

$solr_url = 'quickdraw.laits.utexas.edu:8080/solr/update';

$cs = new Dase_DBO_Collection($db);
foreach ($cs->find() as $c) {
	$c = clone($c);
	$colls[] = $c->ascii_id;
}


//can enter collections on command line
if (isset($argv[1])) {
	array_shift($argv);
	$colls = $argv;
}

$engine = Dase_SearchEngine::get($db,$config);

$i = 0;

foreach ($colls as $coll) {

	$c = Dase_DBO_Collection::get($db,$coll);

	if ($c) {
		foreach ($c->getItems() as $item) {
			$i++;
			$item = clone($item);
			print $c->collection_name.':'.$item->serial_number.':'.$item->buildSearchIndex(false);
			print " $i\n";
		}
		print "\ncommitting indexes for $c->collection_name ";
		$engine->commit();
		print "...done\n\n";
	}
}

