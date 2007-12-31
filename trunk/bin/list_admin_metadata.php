<?php

include 'cli_setup.php';

$a = new Dase_DB_Attribute;
$a->collection_id = 0;
foreach ($a->find() as $att) {
	print $att->ascii_id . "\n";
}

