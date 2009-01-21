<?php

include 'config.php';

$coll = 'itsprop';

Dase_Timer::start();
$c = Dase_DBO_Collection::get($coll);
foreach ($c->getItems() as $item) {
	$entry = new Dase_Atom_Entry_Item;
	$item->injectAtomEntryData($entry,$c);
	$r = $entry->asXml();
}

print Dase_Timer::getElapsed();
