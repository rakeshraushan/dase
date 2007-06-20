<?php
ini_set('include_path',ini_get('include_path').':./../lib:'); 
define('DASE_PATH','..');

include 'Dase/Remote.php';
include 'Dase/DB/Collection.php';

$coll = new Dase_DB_Collection;
foreach ($coll->getAll() as $c) {
	print $c->id;
	$res = file_get_contents("http://littlehat.com/dase/ajax/attribute_tallies?coll_id=$c->id");
	print "\n";
}



