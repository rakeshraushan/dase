#!/usr/bin/php
<?php
$database = 'dase_prod';
include 'cli_setup.php';

$i = 0;

$att = new Dase_DB_Attribute;
foreach ($att->getAll() as $arow) {
	$val = new Dase_DB_Value;
	$val->attribute_id = $arow['id'];
	foreach ($val->findAll() as $row) {
		$v = new Dase_DB_Value($row);
		$v->value_text_md5 = md5($row['value_text']);
		if ($row['value_text_md5'] != $v->value_text_md5) {
			$v->update();
			$i++;
			print "$i\n";
		}
	}
}
