#!/usr/bin/php
<?php
$database = 'dase_prod';
include 'cli_setup.php';

$i = 0;

$att = new Dase_DB_Attribute;
foreach ($att->getAll() as $a) {
	$val = new Dase_DB_Value;
	$val->attribute_id = $a->id;
	foreach ($val->find() as $v) {
		if ($v->value_text_md5 != md5($v->value_text)) {
			$v->value_text_md5 = md5($v->value_text);
			$v->update();
			$i++;
			print "$i\n";
		}
	}
}
