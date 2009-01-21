<?php

include 'config.php';

$coll = 'itsprop';

Dase_Timer::start();
$c = Dase_DBO_Collection::get($coll);
foreach ($c->getItems() as $item) {
	$entry = new Dase_Atom_Entry_Item;
	$item->injectAtomEntryData($entry,$c);
	$atom = new Dase_DBO_ItemAsAtom;
	//should find or create
	$atom->item_id = $item->id;
	$atom->item_type_ascii_id = $item->getItemType()->ascii_id;
	$atom->relative_url = 'item/'.$c->ascii_id.'/'.$item->serial_number;
	$atom->updated = date(DATE_ATOM);
	$atom->xml = $entry->asXml($entry->root); //so we don't get xml declaration
	$atom->insert();
}

