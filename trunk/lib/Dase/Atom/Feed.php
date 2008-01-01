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

	function setSubtitle($text='') {
		if ($this->subtitle_is_set) {
			throw new Dase_Atom_Exception('subtitle is already set');
		} else {
			$this->subtitle_is_set = true;
		}
		if ($text) {
			$subtitle = $this->addElement('subtitle',$text);
			$subtitle->setAttribute('type','text');
		} else {
			$subtitle = $this->addElement('subtitle');
			$subtitle->setAttribute('type','xhtml');
			//results in namespace prefixes which messes up some aggregators
			//return $this->addChildElement($subtitle,'xhtml:div','',Dase_Atom::$ns['xhtml']);
			$div = $subtitle->appendChild($this->dom->createElement('div'));
			$div->setAttribute('xmlns',Dase_Atom::$ns['xhtml']);
			return $div;
			//note that best practice here is to use simplexml 
			//to add subtitle to the returned div
		}
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
		if ($this->entries) {
			foreach ($this->entries as $entry) {
				$this->root->appendChild($entry->root);
			}
		}
		return parent::asXml();
	}

}
