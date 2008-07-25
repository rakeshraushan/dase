#!/usr/bin/php
<?php
//$database = 'dase_prod';
include 'cli_setup.php';

$result = array();

$sx = simplexml_load_file('search.xml');


foreach ($sx->query as $query) {
	if (isset($query['attribute'])) {
		print "{$query['attribute']} : $query\n";
	} else {
		print "$query\n";
	}
}
$t = Dase_Timer::start();
//get search xml doc
$term = $argv[1];
if (!$term) { die ('no term'); }
$term = "%$term%";

$c = new Dase_DB_Collection;
foreach ($c->getAll() as $coll) {
	search($term,$coll->id);
}


function search($term,$coll_id) {
	$st = new Dase_DB_SearchTable;
	$st->addWhere('value_text',$term,'like');
	$st->addWhere('collection_id',$coll_id,'=');
	foreach ($st->find() as $search_table) {
		print "$coll_id : $search_table->item_id\n";
	}
}

print Dase_Timer::getElapsed();
