<?php

include 'cli_setup.php';

$c = Dase_DB_Collection::get('bsls_collection');
$a = Dase_DB_Attribute::getAdmin('admin_checksum');
$i = new Dase_DB_Item;
$i->collection_id = $c->id;
foreach ($i->findAll() as $row) {
	$v = new Dase_DB_Value;
	$v->item_id = $row['id'];
	$v->attribute_id = $a->id;
	if ($v->findOne()) {
		//print $v->value_text . "\n";	
		$hash[$v->value_text] = 1;
	} else {
		print "no md5 for {$row['serial_number']}\n";
	}
	$mf = new Dase_DB_MediaFile;
	$mf->size = 'mp3';
	$mf->item_id = $row['id'];
	$mf->findOne();
	$path = $c->path_to_media_files . "/mp3/" . $mf->filename;
	if (isset($hash[md5_file($path)])) {
		print "match for {$row['serial_number']}\n";
	} else {
		print "NO match for {$row['serial_number']}\n";
		$v->value_text = md5_file($path);
		$v->update();
	}
}

