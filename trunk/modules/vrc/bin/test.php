#!/usr/bin/php
<?php
$database = 'dase_prod';
include 'cli_setup.php';
$coll = new Dase_DBO_Collection;
$coll->ascii_id = 'vrc';
$coll->findOne();

print_r($coll->getJsonData('managers'));exit;

$item = new Dase_DB_Item;
$item->collection_id = $coll->id;
foreach ($item->find() as $it) {
	$count = $it->getMediaCount();
	print "$it->serial_number - $count\n";
}
