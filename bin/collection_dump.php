<?php

include 'config.php';

$c = new Dase_DBO_Collection($db);
$c->orderBy('item_count ASC');

foreach ($c->find() as $coll) {
	$filename = 'collection_dumps/'.$coll->ascii_id.'.xml';
	file_put_contents($filename,$coll->xmlDump());
	print "created $filename\n";
}

