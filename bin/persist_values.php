<?php

$database = 'dase_prod';
include 'cli_setup.php';

//print Dase_DBO_Value::persistAll();
$c = Dase_DBO_Collection::get('chinese_507');
foreach ($c->getItems() as $item) {
	foreach ($item->getValues() as $val) {
		print $val->persist()->p_serial_number . " updated\n";
	}
}
