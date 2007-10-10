<?php

$database = 'dase_prod';
include 'cli_setup.php';

//$coll = 'bsls_collection';
$coll = 'efossils_collection';
$c = Dase_DB_Collection::get($coll);

$status = new Dase_DB_ItemStatus;
$status->status = 'marked_for_delete';
$status->findOne();

$item = new Dase_DB_Item;
$item->collection_id = $c->id;
$item->status_id = $status->id;
foreach ($item->findAll() as $row) {
	$doomed = new Dase_DB_Item($row);
	print "DELETING $doomed->serial_number\n";
	$doomed->deleteValues();
	$doomed->deleteMedia();
	$doomed->delete();
}


