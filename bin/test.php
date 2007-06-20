<?php
ini_set('include_path',ini_get('include_path').':./../lib:'); 
define('DASE_PATH','..');

include 'Dase/DB/Collection.php';
include 'Dase/DB/Attribute.php';
include 'Dase/Log.php';



$collection = new Dase_DB_Collection;
foreach ($collection->getAll() as $coll) {
	foreach ($coll->getAttributes() as $att) {
		$count = $att->getValueCount();
		print "$coll->ascii_id : $att->ascii_id $count \n";
	}
}

