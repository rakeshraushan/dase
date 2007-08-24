#!/usr/bin/php
<?php
//$database = 'dase_prod';
$coll_ascii_id = 'test_collection';
include 'cli_setup.php';
define('APP_ROOT', 'http://quickdraw.laits.utexas.edu/dase');
define('MEDIA_ROOT', '/mnt/www-data/dase/media');

$coll = new Dase_DB_Collection;
$coll->ascii_id = $coll_ascii_id;
if ($coll->findOne()) {


	/*
	$i = new Dase_DB_Item;
	$i->collection_id = $coll->id;
	foreach ($i->findAll() as $row) {
		print "deleting {$row['serial_number']} id $coll->ascii_id\n";
		$doomed = new Dase_DB_Item($row);
		$doomed->deleteValues();
		$doomed->delete();
	}
	exit;
	 */





	$row = 1;
	$handle = fopen("GLOSSARY.csv", "r");
	while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
		$term = trim(mb_convert_encoding($data[0], "UTF-8", "cp1252"));
		$def = trim(mb_convert_encoding($data[1], "UTF-8", "cp1252"));
		$v = new Dase_DB_Value();
		$v->attribute_id = Dase_DB_Attribute::get($coll_ascii_id,'glossary_term')->id;
		$v->value_text = $term;
		if ($v->findOne()) {
			print "found " . $v->value_text . "\n";;
		} else {
			$v->value_text = strtolower($term);
			if ($v->findOne()) {
				print "found " . $v->value_text . "\n";;
			} else {
				print "creating " . $v->value_text . "\n";;
				$item = $coll->createNewItem();
				if ($item->setType('glossary')) {
					$item->setValue('glossary_term',$term);
					$item->setValue('glossary_definition',$def);
					$uri_term = strtolower(str_replace(' ','_',$term));
					$item->setValue('resource_uri',"/resources/glossary/$uri_term");
				}
				$item->buildSearchIndex();
				print "created $item->serial_number\n";
			}
		}
	}
	fclose($handle);
}
