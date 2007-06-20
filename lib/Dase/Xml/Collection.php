<?php

class Dase_Xml_Collection {  

	function __construct($ascii_id = '') {
		$this->id = $ascii_id;
	}

	function getId() {
		return $this->id;
	}
	
	public static function getAll() {
		$collection_xml = DASE_PATH . '/xml/collections.xml';
		if (file_exists($collection_xml)) {
//			return file_get_contents($collection_xml);
		}
		$writer = new XMLWriter();
		$writer->openMemory();
		$writer->setIndent(true);
		$writer->startDocument('1.0','UTF-8');

		$coll = new Dase_DB_Collection;
		$coll->orderBy('collection_name');
		$colls = $coll->getAll();
		$writer->startElement('collections');
		$writer->writeAttribute('count',count($colls));
		foreach ($colls as $found) {
			$writer->startElement('collection');
			$writer->writeAttribute('ascii_id',$found->ascii_id);
			$writer->writeAttribute('collection_name',$found->collection_name);
			$writer->writeAttribute('is_public',$found->is_public);
//			$writer->writeAttribute('item_count',$found->getItemCount());
			$writer->endElement();
		}
		$writer->endElement();
		$writer->endDocument();
		$xml = $writer->flush(true);
		$handle = fopen($collection_xml, "w+") or die('no go');
		if (fwrite($handle, $xml) === FALSE) {
			die('no go write');
		}
		fclose($handle);
		return $xml;
	}
}
