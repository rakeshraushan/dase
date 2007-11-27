#!/usr/bin/php
<?php

$database = 'dase_prod';
include 'cli_setup.php';

$coll = Dase_DB_Collection::get('vrc_collection');

$pdo = new PDO("dblib:host=$host;dbname=$name", $user, $pass);

$sql = "
	SELECT *
	FROM tblMaterialLU 
	";

$mat = array();
$st = $pdo->prepare($sql);
$st->setFetchMode(PDO::FETCH_ASSOC);
$st->execute();
while ($row = $st->fetch()) {
	$mat[$row['mat_abbr_PK']] = $row['mat_desc'];
}

$sql = "
	SELECT  
	acc_num_PK, 
	acc_material
	FROM tblAccession 
	WHERE acc_digital_num != ''
	AND acc_material like '%.%'
	";

$st = $pdo->prepare($sql);
$st->setFetchMode(PDO::FETCH_ASSOC);
$st->execute();
while ($row = $st->fetch()) {
	$item[$row['acc_num_PK']] = $row['acc_material'];
}

$full = array();
foreach ($item as $k => $v) {
	$mat_array = explode('.',$v);
	foreach ($mat_array as $abbr) {
		if (isset($mat[$abbr])) {
			$full[$k][] = $mat[$abbr];
		} else {
			print "no definition for $abbr\n";
		}
	}
}


$a = new Dase_DB_Attribute;
$a->collection_id = $coll->id;
$a->ascii_id = 'mat_desc';
$a->findOne();

foreach ($full as $pk => $arr) {
	$it = new Dase_DB_Item;
	$it->serial_number = $pk;
	$it->collection_id = $coll->id;
	if ($it->findOne()) {
		foreach ($arr as $material) {
			$v = new Dase_DB_Value;
			$v->item_id = $it->id;
			$v->value_text = $material;
			$v->attribute_id = $a->id;
			if (!$v->findOne()) {
				print "inserted $material for $pk\n";
				$v->value_text_md5 = md5($material);
				$v->insert();
			}
		}
		$it->buildSearchIndex();
	}
}

