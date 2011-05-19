<?php

include 'config.php';

$coll = 'ut_publications';
$coll = 'philosophy_reading_room';

$c = Dase_DBO_Collection::get($db,$coll);

print "$c->collection_name ($c->item_count)\n\n";

foreach ($c->getItems() as $item) {
	$item = clone($item);
	print "deleting ".$item->serial_number."\n";
	print $item->expunge();
}
