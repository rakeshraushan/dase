<?php

$q = trim($argv[1]);

$r = new XMLReader();
$r->open('../xml/vrc_collection.xml');
$doc = new DOMDocument('1.0');
$root = $doc->createElement('result');
$doc->appendChild($root);
while ($r->read()) {
	if ($r->nodeType == XMLREADER::ELEMENT && $r->localName == 'item') {
		$item = $doc->importNode($r->expand(),true);
		$r->moveToAttribute('serial_number');
		$sernum = $r->value;
	}
	if ($r->nodeType == XMLREADER::ELEMENT && $r->localName == 'metadata') {
		$r->read();
		$vt = $r->value;
		if (false !== strpos($vt,$q)) {
			$root->appendChild($item);
		}
	}
}

$doc->formatOutput = true;
echo $doc->saveXML() . "\n";
