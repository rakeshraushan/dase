<?php
$database = 'dase_prod';
require_once 'cli_setup.php';

$st = new Dase_DB_SearchTable;
foreach ($st->getAll() as $row) {
	$l = $row['last_update'];
	$i = $row['item_id'];
	if ($l) {
		updateItem($i,$l);
	}
}

function updateItem($item_id,$last) {
	$item = new Dase_DB_Item;
	$item->load($item_id);
	if ($item->last_update != $last) {
		$item->last_update = $last;
		$item->update();
		print "set last_update on item $item_id\n";
	}
}
