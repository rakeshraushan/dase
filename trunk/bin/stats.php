#!/usr/bin/php
<?php
$database = 'dase_prod';
include 'cli_setup.php';

$tag_item = new Dase_DB_TagItem;
$tot = array();
foreach ($tag_item->getAll() as $t) {
	$tag_item = new Dase_DB_TagItem($t);
	if (isset($tot[$tag_item->p_collection_ascii_id])) {
		$tot[$tag_item->p_collection_ascii_id]++;
	} else {
		$tot[$tag_item->p_collection_ascii_id] = 1;
	}
}

arsort($tot);
$top_ten = array_slice($tot,0,20);

foreach ($top_ten as $k => $v) {
	$coll = Dase_DB_Collection::get($k);
	print "$coll->collection_name has $v items saved in cart/user collection/slideshow\n";
}
