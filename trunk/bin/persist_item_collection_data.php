<?php

include 'config.php';

$c = Dase_DBO_Collection::get($db,'wpa');

$i = 0;
foreach ($c->getItems() as $item) {
	$item = clone($item);
	$item->p_collection_ascii_id = $c->ascii_id;
	$item->collection_name = $c->collection_name;
	if ($item->update()) {
		$i++;
	}
}
print "updated $i items in $c->collection_name\n";

