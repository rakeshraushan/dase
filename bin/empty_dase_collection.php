<?php

include 'config.php';

$coll = 'india_images';

$c = Dase_DBO_Collection::get($db,$coll);

print "$c->collection_name ($c->item_count)\n\n";

foreach ($c->getItems() as $item) {
	print "deleting ".$item->serial_number."\n";
	print $item->expunge();
}