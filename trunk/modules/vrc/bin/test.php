#!/usr/bin/php
<?php
$database = 'dase_prod';
include 'cli_setup.php';
$coll = new Dase_DB_Collection;
$coll->ascii_id = 'vrc_collection';
$coll->findOne();


$item = new Dase_DB_Item;
$item->collection_id = $coll->id;
foreach ($item->find() as $it) {
	$count = $it->getMediaCount();
	print "$it->serial_number - $count\n";
}
