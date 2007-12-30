<?php
class Dase_Atom_Entry extends Dase_Atom
{
	public $dom;
	public $root;
	private $content_is_set;
	private $published_is_set;
	private $source_is_set;
	private $summary_is_set;

	function __construct($dom) {
		$this->root = $dom->createElement('entry');
		$this->dom = $dom;
	}

	function setContent($text='') {
		if ($this->content_is_set) {
			throw new Dase_Atom_Exception('content is already set');
		} else {
			$this->content_is_set = true;
		}
		if ($text) {
			$content = $this->addElement('content',$text);
			$content->setAttribute('type','text');
		} else {
			$content = $this->addElement('content');
			$content->setAttribute('type','xhtml');
			//results in namespace prefixes which messes up some aggregators
			//return $this->addChildElement($content,'xhtml:div','',Dase_Atom::$ns['xhtml']);
			$div = $content->appendChild($this->dom->createElement('div'));
			$div->setAttribute('xmlns',Dase_Atom::$ns['xhtml']);
			return $div;
			//note that best practice here is to use simplexml 
			//to add content to the returned div
		}
	}

	function setPublished() {
		if ($this->published_is_set) {
			throw new Dase_Atom_Exception('published is already set');
		} else {
			$this->published_is_set = true;
		}
		//finish!!!!!!!!!!
	}

	function setSource() {
		if ($this->source_is_set) {
			throw new Dase_Atom_Exception('source is already set');
		} else {
			$this->source_is_set = true;
		}
		//finish!!!!!!!!!!
	}

	function setSummary($text) {
		if ($this->summary_is_set) {
			throw new Dase_Atom_Exception('summary is already set');
		} else {
			$this->summary_is_set = true;
		}
		$summary = $this->addElement('summary',$text);
	}
}
