<?php
require_once 'cli_setup.php';

/*
$url = "http://quickdraw.laits.utexas.edu/dase/api/xml/efossils_collection";
$remote = new Dase_Remote($url,'xxx','xxx');
print ($remote->get());
 */

$coll = new Dase_DB_Collection;
foreach ($coll->getAll() as $c) {
	$filename = "/export/home/pkeane/dase_backup/{$c['ascii_id']}.xml";
	$mem = memory_get_usage(true);
	print "working on {$c['collection_name']} ($mem)\n";
	$collection = new Dase_DB_Collection;
	$collection->load($c['id']);
	file_put_contents($filename,$collection->xmlDump());
}
