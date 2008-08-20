<?php

//include 'laptop_cli.php';
$database = 'dase_prod';
include 'cli_setup.php';

$c = Dase_DBO_Collection::get('cumbojd');

$item = new Dase_DBO_Item;
$item->collection_id = $c->id;
$item->status = 'delete';
foreach ($item->find() as $doomed) {
	print "DELETING $doomed->serial_number\n";
	$doomed->expunge();
}


