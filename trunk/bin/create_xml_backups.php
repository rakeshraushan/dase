<?php
$database = 'dase_prod'; 
$backup_dir = '../xml';
//$database = <database>;
//$backup_dir = <backup_dir>;

require_once 'cli_setup.php';

/*
$url = "http://quickdraw.laits.utexas.edu/dase/api/<coll_ascii_id>";
$remote = new Dase_Remote($url,'xxx','xxx');
print ($remote->get());
 */

//check for last_update to see if we need to regen

$coll = new Dase_DB_Collection;
//$coll->ascii_id = 'efossils_collection';
//foreach ($coll->findAll() as $c) {
foreach ($coll->getAll() as $c) {
	$filename = "$backup_dir/{$c['ascii_id']}.xml";
	$mem = memory_get_usage(true);
	print "working on {$c['collection_name']} ($mem)\n";
	$collection = new Dase_DB_Collection;
	$collection->load($c['id']);
	file_put_contents($filename,$collection->getXml());
}
