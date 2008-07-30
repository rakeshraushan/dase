<?php

include 'laptop_cli.php';

function capFirst (&$item,$key) {
	$item = ucfirst($item);
}

foreach (array('search_table','admin_search_table','item','attribute','item_type','search_cache') as $table) {
	//$sql = "ALTER TABLE $table DROP `collection_ascii_id` VARCHAR( 200 ) NOT NULL DEFAULT 'test'";
	$sql = "ALTER TABLE $table ADD `collection_ascii_id` VARCHAR( 200 ) NOT NULL DEFAULT 'test'";
	try {
		Dase_DBO::query($sql);
		print "altered table $table\n";
	} catch (Dase_DBO_Exception $e) {
		print $e;
	}


	$parts = explode('_',$table);
	array_walk($parts,'capFirst');
	$class = 'Dase_DBO_'.implode('',$parts);

	$item = new $class;
	foreach ($item->find() as $it) {
		$c = new Dase_DBO_Collection;
		if ($c->load($it->collection_id)) {
			print "updating $it->serial_number ($c->ascii_id)\n";
			$it->collection_ascii_id = $c->ascii_id;
			$it->update();
		}
	}
}


