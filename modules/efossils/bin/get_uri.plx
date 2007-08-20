#!/usr/bin/php
<?php
$database = 'dase_prod';
include 'cli_setup.php';

$collection = new Dase_DB_Collection;
$collection->ascii_id = 'efossils_collection';
$collection->findOne();

$site = new Dase_DB_Attribute;
$site->collection_id = $collection->id;
$site->ascii_id = 'site';
$site->findOne();

$site_section = new Dase_DB_Attribute;
$site_section->collection_id = $collection->id;
$site_section->ascii_id = 'site_section';
$site_section->findOne();

$text_level = new Dase_DB_Attribute;
$text_level->collection_id = $collection->id;
$text_level->ascii_id = 'text_level';
$text_level->findOne();


$item = new Dase_DB_Item;
$item->collection_id = $collection->id;
foreach ($item->findAll() as $row) {
	print "ITEM: {$row['serial_number']}\n";

	$type = new Dase_DB_ItemType;
	$type->load($row['item_type_id']);
	if ($type->name) {
		print "\ttype: $type->name\n";
	}	

	$val = new Dase_DB_Value;
	$val->item_id = $row['id'];
	$val->attribute_id = $site->id;
	foreach($val->findAll() as $site_row) {
		print "\tsite: {$site_row['value_text']}\n";
	}
	$val = new Dase_DB_Value;
	$val->item_id = $row['id'];
	$val->attribute_id = $site_section->id;
	foreach($val->findAll() as $site_section_row) {
		print "\tsite section: {$site_section_row['value_text']}\n";
	}
	$val = new Dase_DB_Value;
	$val->item_id = $row['id'];
	$val->attribute_id = $text_level->id;
	foreach($val->findAll() as $text_level_row) {
		print "\ttext level: {$text_level_row['value_text']}\n";
	}

	makeURI($site_row['value_text'],$site_section_row['value_text'],$text_level_row['value_text']);
}

function makeURI($a,$b,$c) {
	$a = strtolower($a) ? $a : "{site}";
	$b = strtolower($b) ? $b : "{site_section}";
	$c = strtolower($c) ? $c : "{text_level}";
print "URI: $a/$b/$c\n";
}

