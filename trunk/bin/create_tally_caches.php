<?php
include 'cli_setup.php';

$coll = new Dase_DB_Collection;
foreach ($coll->getAll() as $row) {
	$c = new Dase_DB_Collection($row);
	print $c->id;
	$res = file_get_contents("http://littlehat.com/dase/ajax/attribute_tallies?coll_id=$c->id");
	print "\n";
}



