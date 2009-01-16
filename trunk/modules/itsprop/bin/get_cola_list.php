#!/usr/bin/php
<?php

include 'config.php';

$user = 'pkeane';
$pass = 'itsprop8';

$url = "http://web.austin.utexas.edu/cola/xml/unit-heads.xml";
$xml = file_get_contents($url);
$xml = mb_convert_encoding($xml, "CP1252", "UTF-8");

$reader = new XMLReader();
$reader->XML($xml);
$entry = null;
while ($reader->read()) {
	if ($reader->nodeType == XMLREADER::ELEMENT && $reader->localName == 'unit') {
		if ($entry) {
			print "posting ".$entry->getTitle() ."\n";
			$slug = 'dept-'.$entry->select('dept_id');
			print $entry->postToUrl(APP_ROOT.'/collection/itsprop',$user,$pass,$slug);
			print "\n";
			//print $entry->asXml();
		}
		$entry = new Dase_Atom_Entry_Item;
		$reader->moveToAttribute('id');
		$dept_id = $reader->value;
		$entry->setId('tag:daseproject.org,'.date("Y-m-d").':'.$dept_id);
		$entry->setItemType('department');
		$entry->addMetadata('dept_id',$dept_id);
		$reader->read();
		$dept_name = $reader->value;
		$entry->setTitle($dept_name);
		$entry->addMetadata('dept_name',$dept_name);

	}
	if ($reader->nodeType == XMLREADER::ELEMENT && $reader->localName == 'unithead') {
		$reader->read();
		$dept_chair = $reader->value;
		$entry->addMetadata('dept_chair',$dept_chair);
	}
	if ($reader->nodeType == XMLREADER::ELEMENT && $reader->localName == 'phone') {
		$reader->read();
		$dept_phone = $reader->value;
		$entry->addMetadata('dept_phone',$dept_phone);
	}
	if ($reader->nodeType == XMLREADER::ELEMENT && $reader->localName == 'email') {
		$reader->read();
		$dept_email = $reader->value;
		$entry->addMetadata('dept_email',$dept_email);
	}
}
