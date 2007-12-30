<?php
class Dase_Atom_Feed extends Dase_Atom
{
	public $dom;
	public $entries = array();
	public $root;
	private $generator_is_set;
	private $subtitle_is_set;

	function __construct() {
		$dom = new DOMDocument('1.0');
		$this->dom = $dom;
		$this->root = $this->dom->appendChild($this->dom->createElementNS(Dase_Atom::$ns['atom'],'feed'));
	}

	function addEntry() {
		$entry = new Dase_Atom_Entry($this->dom);
		$this->entries[] = $entry;
		return $entry;
	}

	function setGenerator($text,$uri='',$version='') {
		if ($this->generator_is_set) {
			throw new Dase_Atom_Exception('generator is already set');
		} else {
			$this->generator_is_set = true;
		}
		$generator = $this->addElement('generator',$text);
		if ($uri) {
			$generator->setAttribute('uri',$uri);
		}
		if ($version) {
			$generator->setAttribute('version',$version);
		}
	}

	function setSubtitle($text) {
		if ($this->subtitle_is_set) {
			throw new Dase_Atom_Exception('subtitle is already set');
		} else {
			$this->subtitle_is_set = true;
		}
		$this->addElement('subtitle',$text);
	}

	function setOpensearchTotalResults($num) {
		$this->addElement('totalResults',$num,Dase_Atom::$ns['opensearch']);
	}

	function setOpensearchStartIndex($num) {
		$this->addElement('startIndex',$num,Dase_Atom::$ns['opensearch']);
	}

	function setOpensearchItemsPerPage($num) {
		$this->addElement('itemsPerPage',$num,Dase_Atom::$ns['opensearch']);
	}

	function asXml() {
		//attach entries
		foreach ($this->entries as $entry) {
			$this->root->appendChild($entry->root);
		}
		//format output
		$this->dom->formatOutput = true;
		return $this->dom->saveXML();
	}
}
