<?php

$url = "http://dase.laits.utexas.edu/xml/vrc_collection/x81-02496";
$url = "http://dase.laits.utexas.edu/xml/vrc_collection/81-02496";
$sxe = new SimpleXMLElement($url, NULL, TRUE);

if ("no such item" == $sxe) {
	print "no go\n";
} else {
	print count($sxe->item->media_file);

	foreach ($sxe->item->media_file as $mf) {
	//	print "{$mf['size']}\n";
	}
}
