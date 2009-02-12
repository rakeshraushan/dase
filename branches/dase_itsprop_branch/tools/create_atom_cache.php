<?php

include 'config.php';

$coll = 'itsprop';
$app_root = 'http://dev.laits.utexas.edu/dasebeta';
Dase_Config::set('app_root',$app_root);

Dase_Timer::start();
$c = Dase_DBO_Collection::get($coll);
$i = 0;
foreach ($c->getItems() as $item) {
	$item = clone $item;
	$atom = new Dase_DBO_ItemAsAtom;
	$atom->item_id = $item->id;
	if (!$atom->findOne()) {
		$atom->insert();
	}
	$entry = new Dase_Atom_Entry_Item;
	$item->injectAtomEntryData($entry,$c);
	$atom->item_type_ascii_id = $item->getItemType()->ascii_id;
	$atom->relative_url = 'item/'.$c->ascii_id.'/'.$item->serial_number;
	$atom->updated = date(DATE_ATOM);
	$atom->xml = $entry->asXml($entry->root); //so we don't get xml declaration
	$atom->app_root = $app_root;
	$atom->update();
	$i++;
	print "$i. created/updated atom for $item->serial_number\n";
}
print Dase_Timer::getElapsed()." seconds for $i items\n";
