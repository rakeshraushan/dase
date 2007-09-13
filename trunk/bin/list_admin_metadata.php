<?php

include 'cli_setup.php';

$a = new Dase_DB_Attribute;
$a->collection_id = 0;
foreach ($a->findAll() as $row) {
	print $row['ascii_id'] . "\n";
}

