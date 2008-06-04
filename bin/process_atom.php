<?php
include 'cli_setup.php';


$atom = Dase_Atom_Feed::load('american_west.atom');


$collection = new Dase_DBO_Collection;
$collection->ascii_id = $atom->getAscii_id();
if ($collection->findOne()) {
	print "collection exists\n";
	exit;
}
$collection->collection_name = $atom->getName();
$collection->path_to_media_files = 'do not know yet';
$collection->is_public = 1;
$collection->created = date(DATE_ATOM);
$collection->updated = date(DATE_ATOM);
$collection->insert();
print "working on collection $collection->name\n";

foreach ($atom->getAttributes() as $ascii => $name) {
	$att = new Dase_DBO_Attribute;
	$att->collection_id = $collection->id;
	$att->ascii_id = $ascii;
	$att->attribute_name = $name;
	if (!$attribute->findOne()) {
		$att->updated = date(DATE_ATOM);
		$att->insert();
	}
}

foreach ($atom->getAdminAttributes() as $ascii => $name) {
	$att = new Dase_DBO_Attribute;
	$att->collection_id = 0; 
	$att->ascii_id = $ascii;
	$att->attribute_name = $name;
	if (!$attribute->findOne()) {
		$att->updated = date(DATE_ATOM);
		$att->insert();
	}
}

foreach ($atom->entries as $entry) {
	$item = Dase_DBO_Item::create($collection->ascii_id,$entry->getSerialNumber());
	foreach ($entry->getMetadata() as $ascii => $set) {
		foreach ($set['values'] as $v) {
			$item->setValue($ascii,$v);
		}
	}
	foreach ($entry->getMedia() as $med) {
		$media_file = new Dase_DBO_MediaFile;
		$media_file->item_id = $item->id;
		$media_file->filename = basename($med['href']);
		$media_file->width = $med['width'];
		$media_file->height = $med['height'];
		$media_file->size = $med['label'];
		$media_file->mime_type = $med['type'];
		$media_file->p_serial_number = $item->serial_number;
		$media_file->p_collection_ascii_id = $collection->ascii_id;
		$media_file->updated = date(DATE_ATOM);
		$media_file->insert();
	}
}


