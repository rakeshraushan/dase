<?php
class Dase_Xml_Collection  implements Dase_CollectionInterface
{
	public $xml_file;
	public $xml_index_file;
	public $ascii_id;

	public function __construct($ascii_id) {
		$xml_file = DASE_PATH . '/xml/' . $ascii_id . '.xml'; 
		if (file_exists($xml_file)) { 
			$this->ascii_id = $ascii_id;
			$this->xml_file = $xml_file;
		} else {
			throw new Exception('no xml file');
		}
	}

	public static function get($ascii_id) {
		return new Dase_Xml_Collection($ascii_id);
	}

	function getXml() {
		return file_get_contents($this->xml_file);
	}

	public static function create($ascii_id) {
		$c = Dase_DB_Collection::get($ascii_id);
		if ($c->id) {
			file_put_contents(DASE_PATH . '/xml/' . $ascii_id . '.xml',$c->xmlDump());
		} else {
			throw new Exception('no such archive');
		}
	}

	function prepareIndex() {
		$xml_index_file = DASE_PATH . '/xml/' . $this->ascii_id . '_index.xml'; 
		if (file_exists($xml_index_file)) {
			$this->xml_index_file = $xml_index_file;
		} else {
			$this->createIndex();
		}
	}

	function createIndex() {
		if (!$this->ascii_id) {
			return false;
		}
		$r = new XMLReader();
		$r->open($this->xml_file);
		$doc = new DOMDocument('1.0');
		$root = $doc->createElement('result');
		$doc->appendChild($root);
		while ($r->read()) {
			if ($r->nodeType == XMLREADER::ELEMENT && $r->localName == 'item') {
				$item = $doc->appendChild($doc->createElement('item'));
				$r->moveToAttribute('serial_number');
				$item->setAttribute('serial_number',$r->value);
				$composite_vt = '';
			}
			if ($r->nodeType == XMLREADER::ELEMENT && $r->localName == 'metadata') {
				$r->read();
				$composite_vt .= $r->value . " ";
			}
			if ($r->nodeType == XMLREADER::END_ELEMENT && $r->localName == 'item') {
				$item->appendChild($doc->createTextNode($composite_vt));
				$root->appendChild($item);
			}
		}
		$doc->formatOutput = true;
		$this->xml_index_file = DASE_PATH . '/xml/' . $this->ascii_id . '_index.xml'; 
		return file_put_contents($this->xml_index_file,$doc->saveXML());
	}

	function find($query) {
		$sernum_array = array();
		$r = new XMLReader();
		$r->open($this->xml_index_file);
		while ($r->read()) {
			if ($r->nodeType == XMLREADER::ELEMENT && $r->localName == 'item') {
				$r->moveToAttribute('serial_number');
				$sernum = $r->value;
				$r->read();
				if (false !== stripos($r->value,$query)) {
					$r->moveToAttribute('serial_number');
					$sernum_array[] = $sernum;
				}
			}
		}
		return join(',',$sernum_array); 
	}

	function getItemsBySerialNumbers($serial_numbers) {
		$sernum_array= explode(',',$serial_numbers);
		$r = new XMLReader();
		$r->open($this->xml_file);
		$doc = new DOMDocument('1.0');
		$root = $doc->createElement('result');
		$doc->appendChild($root);
		while ($r->read()) {
			if ($r->nodeType == XMLREADER::ELEMENT && $r->localName == 'item') {
				$item = $doc->importNode($r->expand(),true);
				$r->moveToAttribute('serial_number');
				if (in_array($r->value,$sernum_array)) {
					$root->appendChild($item);
				}
			}
		}
		$doc->formatOutput = true;
		return $doc->saveXML();
	}

	function getItemsXmlByType($type_ascii_id) {
		$r = new XMLReader();
		$r->open($this->xml_file);
		$doc = new DOMDocument('1.0');
		$root = $doc->createElement('result');
		$doc->appendChild($root);
		while ($r->read()) {
			if ($r->nodeType == XMLREADER::ELEMENT && $r->localName == 'item') {
				$item = $doc->importNode($r->expand(),true);
				$r->moveToAttribute('item_type');
				if ($type_ascii_id == $r->value) {
					$root->appendChild($item);
				}
			}
		}
		$doc->formatOutput = true;
		return $doc->saveXML();
	}

	function getItemsXmlByAttVal($att_ascii_id,$value_text,$substr = false) {
		$r = new XMLReader();
		$r->open($this->xml_file);
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
				$r->moveToAttribute('attribute_ascii_id');
				if ($att_ascii_id == $r->value) {
					if ($substr) {
						if (false !== strpos($vt,$value_text)) {
							$root->appendChild($item);
						}
					} else {
						if ($vt == $value_text) {
							$root->appendChild($item);
						}
					}
				}
			}
		}
		$doc->formatOutput = true;
		return $doc->saveXML();
	}

	function getAttVals($att_ascii_id) {
		$att_vals = array();
		$r = new XMLReader();
		$r->open($this->xml_file);
		while ($r->read()) {
			if ($r->nodeType == XMLREADER::ELEMENT && $r->localName == 'metadata') {
				$r->moveToAttribute('value_text_md5');
				$vt_md5 = $r->value;
				$r->moveToAttribute('attribute_ascii_id');
				$aa = $r->value;
				$r->read();
				$vt = $r->value;
				if ($att_ascii_id == $aa) {
					$att_vals[$vt_md5] = $vt;
				}
			}
		}
		return $att_vals;
	}
}
