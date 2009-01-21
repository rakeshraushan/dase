<?php

include 'config.php';

$coll = 'itsprop';

Dase_Timer::start();
$c = Dase_DBO_Collection::get($coll);
$feed = new Dase_Atom_Feed;
foreach ($c->getItems() as $item) {
	//$entry = new Dase_Atom_Entry_Item;
//	$item->injectAtomEntryData($entry,$c);
	$item->injectAtomEntryData($feed->addEntry(),$c);
}

print $feed->asXml();
print Dase_Timer::getElapsed();
