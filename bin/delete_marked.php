<?php

$database = 'dase_prod';
include 'cli_setup.php';

//$coll = 'bsls_collection';
//$coll = 'south_asia';
//$coll = 'medieval';
//$coll = 'early_american_history';
$coll = 'kerkhoff';
$c = Dase_DBO_Collection::get($coll);

$status = new Dase_DBO_ItemStatus;
$status->status = 'marked_for_delete';
$status->findOne();

$item = new Dase_DBO_Item;
$item->collection_id = $c->id;
$item->status = 'delete';
foreach ($item->find() as $doomed) {
	print "DELETING $doomed->serial_number\n";
	$doomed->expunge();
}


