<?php
class Dase_Atom_Entry extends Dase_Atom
{
	protected $content_is_set;
	protected $published_is_set;
	protected $source_is_set;
	protected $summary_is_set;

	//note: dom is the dom object and root is the root
	//element of the document.  If this entry is part of
	//a feed, then the root means the root of the feed. If
	//this is a free-standing entry document, it means the
	//'entry' element
	
	function __construct(DOMDocument $dom=null,DOMElement $root=null)
	{
		if (!$dom && $root) {
			throw new Dase_Atom_Exception('dom doc needs to be passed into constructor with domnode');
		}
		if ($dom) {
			$this->dom = $dom;
			if ($root) {
				$this->root = $root;
			} else {
				$this->root = $dom->createElementNS(Dase_Atom::$ns['atom'],'entry');
			}
		} else {
			//creator object (standalone entry document)
			$dom = new DOMDocument('1.0','utf-8');
			$this->root = $dom->appendChild($dom->createElementNS(Dase_Atom::$ns['atom'],'entry'));
			$this->dom = $dom;
		}
	}

	public static function load($xml) {
		//reader object
		$dom = new DOMDocument('1.0','utf-8');
		$dom->load($xml);
		return new Dase_Atom_Entry($dom);
	}

	function __get($var) {
		//allows smarty to invoke function as if getter
		$classname = get_class($this);
		$method = 'get'.ucfirst($var);
		if (method_exists($classname,$method)) {
			return $this->{$method}();
		} else {
			return parent::__get($var);
		}
	}

	function setContent($text='',$type='text')
	{
		if ($this->content_is_set) {
			throw new Dase_Atom_Exception('content is already set');
		} else {
			$this->content_is_set = true;
		}
		if ($text) {
			if ('html' == $type) {
				$content = $this->addElement('content',htmlentities($text,ENT_COMPAT,'UTF-8'));
				$content->setAttribute('type','html');
			} else {
				$content = $this->addElement('content',$text);
				$content->setAttribute('type','text');
			}
		} else {
			$content = $this->addElement('content');
			$content->setAttribute('type','xhtml');
			//results in namespace prefixes which messes up some aggregators
			//return $this->addChildElement($content,'xhtml:div','',Dase_Atom::$ns['xhtml']);
			$div = $content->appendChild($this->dom->createElement('div'));
			$div->setAttribute('xmlns',Dase_Atom::$ns['h']);
			return $div;
			//note that best practice here is to use simplexml 
			//to add content to the returned div
		}
	}

	function setMediaContent($url,$mime) {
		if ($this->content_is_set) {
			throw new Dase_Atom_Exception('content is already set');
		} else {
			$this->content_is_set = true;
		}
		$content = $this->addElement('content');
		$content->setAttribute('src',$url);
		$content->setAttribute('type',$mime);
	}

	function setPublished($text)
	{
		if ($this->published_is_set) {
			throw new Dase_Atom_Exception('published is already set');
		} else {
			$this->published_is_set = true;
		}
		$published = $this->addElement('published',$text);
	}

	function setSource()
	{
		if ($this->source_is_set) {
			throw new Dase_Atom_Exception('source is already set');
		} else {
			$this->source_is_set = true;
		}
		//finish!!!!!!!!!!
	}

	function setSummary($text)
	{
		if ($this->summary_is_set) {
			throw new Dase_Atom_Exception('summary is already set');
		} else {
			$this->summary_is_set = true;
		}
		$summary = $this->addElement('summary',$text);
	}

	function getContent() 
	{
		return $this->getAtomElementText('content');
	}

	function getId() 
	{
		return $this->getAtomElementText('id');
	}
}
