<?php


exit;


ini_set('display_errors','on');
error_reporting(E_ALL);

$collection_ascii_id = 'efossils_collection';

$c = Dase_DB_Collection::get($collection_ascii_id);

$type = new Dase_DB_ItemType;
$type->ascii_id = 'glossary';
$type->findOne();
$i = 0;
$item = new Dase_DB_Item;
$item->item_type_id = $type->id;
foreach ($item->findAll() as $row) {
	$jpeg = '';
	$custom = '';
	$i++;
	$mf =  new Dase_DB_MediaFile;
	$mf->item_id = $row['id'];
	$mf->size = 'jpeg';
	$jpeg = $mf->findOne();
	$mf2 =  new Dase_DB_MediaFile;
	$mf2->item_id = $row['id'];
	$mf2->size = 'custom';
	$custom = $mf2->findOne();

	if ($jpeg && $custom && ($jpeg->width > 300 || $jpeg->height > 300)) {
		$jpeg->resize('300x300');
		print $jpeg->width . "x" . $jpeg->height . "\n";
	}
}

