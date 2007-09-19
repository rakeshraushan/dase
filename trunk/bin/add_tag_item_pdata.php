#!/usr/bin/php
<?php
$database = 'dase_prod';
include 'cli_setup.php';

$tag_item = new Dase_DB_TagItem;
$i = 0;
$j = 0;
foreach ($tag_item->getAll() as $t) {
	$tag_item = new Dase_DB_TagItem($t);
	$item = new Dase_DB_Item;
	$item->load($t['item_id']);
	if (!$item->serial_number) {
		$i++;
		print "***************************************** deleted missing item ($i)\n";
		$tag_item->delete();
	} else {
		$tag_item->p_serial_number = $item->load($t['item_id'])->serial_number;
		$tag_item->p_collection_ascii_id = $item->getCollection()->ascii_id;
		$tag_item->update();
		$j++;
		print "updated! ($j)\n";
	}
}

