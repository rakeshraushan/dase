<?php

include 'laptop_cli.php';

$item = new Dase_DBO_Item;
//$item->collection_id = $c->id;
$item->status = 'delete';
foreach ($item->find() as $doomed) {
	print "DELETING $doomed->serial_number\n";
	$doomed->expunge();
}


