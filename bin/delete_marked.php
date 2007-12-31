<?php

$database = 'dase_prod';
include 'cli_setup.php';

//$coll = 'bsls_collection';
//$coll = 'south_asia_collection';
$coll = 'test_collection';
$c = Dase_DB_Collection::get($coll);

$status = new Dase_DB_ItemStatus;
$status->status = 'marked_for_delete';
$status->findOne();

$item = new Dase_DB_Item;
$item->collection_id = $c->id;
$item->status_id = $status->id;
foreach ($item->find() as $doomed) {
	print "DELETING $doomed->serial_number\n";
	$doomed->deleteValues();
	$doomed->deleteMedia();
	$doomed->delete();
}


