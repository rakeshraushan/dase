<?php
class Dase_Atom
{
	//these need to be public
	//so Feed can access Entry's root
	//upon serialization
	public $dom;
	public $root;

	protected $id;
	protected $rights_is_set;
	protected $title_is_set;
	protected $updated_is_set;
	public static $ns = array(
		'app' => 'http://www.w3.org/2007/app',
		'atom' => 'http://www.w3.org/2005/Atom',
		'dc' => 'http://purl.org/dc/elements/1.1/',
		'dcterms' => 'http://purl.org/dc/terms/',
		'd' => 'http://daseproject.org/ns/1.0',
		'gd' =>'http://schemas.google.com/g/2005',
		'gsx' =>'http://schemas.google.com/spreadsheets/2006/extended',
		'h' => 'http://www.w3.org/1999/xhtml',
		'media' => 'http://search.yahoo.com/mrss/',
		'opensearch' => 'http://a9.com/-/spec/opensearch/1.1/',
		'rdf' => 'http://www.w3.org/1999/02/22-rdf-syntax-ns#',
		'thr' => 'http://purl.org/syndication/thread/1.0',

	);

	function __get($var) {
		//allows smarty to invoke function as if getter
		$classname = get_class($this);
		$method = 'get'.ucfirst($var);
		if (method_exists($classname,$method)) {
			return $this->{$method}();
		}
	}

	//convenience method for atom elements
	function addElement($tagname,$text='',$ns='') 
	{
		if ($ns) {
			$elem = $this->root->appendChild($this->dom->createElementNS($ns,$tagname));
		} else {
			$elem = $this->root->appendChild($this->dom->createElementNS(Dase_Atom::$ns['atom'],$tagname));
			//$elem = $this->root->appendChild($this->dom->createElement($tagname));
		}
		if ($text || '0' === $text) { //so '0' works
			$elem->appendChild($this->dom->createTextNode($text));
		}
		return $elem;
	}

	//convenience method for atom elements
	function addChildElement($parent,$tagname,$text='',$ns='') 
	{
		if (!$ns) {
			$ns = Dase_Atom::$ns['atom'];
		}
		$elem = $parent->appendChild($this->dom->createElementNS($ns,$tagname));
		if ($text) {
			$elem->appendChild($this->dom->createTextNode($text));
		}
		return $elem;
	}

	function addAuthor($name_text='',$uri_text='',$email_text='') 
	{
		$author = $this->addElement('author');
		if (!$name_text) {
			$name_text = 'DASe (Digital Archive Services)';
			$uri_text = 'http://daseproject.org';
			$email_text = 'admin@daseproject.org';
		}
		$this->addChildElement($author,'name',$name_text);
		if ($uri_text) {
			$this->addChildElement($author,'uri',$uri_text);
		}
		if ($email_text) {
			$this->addChildElement($author,'email',$email_text);
		}
	}

	function addCategory($term,$scheme='',$label='') 
	{
		$cat = $this->addElement('category');
		$cat->setAttribute('term',$term);
		if ($scheme) {
			$cat->setAttribute('scheme',$scheme);
		}
		if ($label) {
			$cat->setAttribute('label',$label);
		}
	}

	function addContributor($name_text,$uri_text = '',$email_text = '') 
	{
		$contributor = $this->addElement('contributor');
		$this->addChildElement($contributor,'name',$name_text);
		if ($uri_text) {
			$this->addChildElement($contributor,'uri',$uri_text);
		}
		if ($email_text) {
			$this->addChildElement($contributor,'email',$email_text);
		}
	}

	function getId() {
		return $this->getAtomElementText('id');
	}

	function setId($text) 
	{
		if ($this->id) {
			throw new Dase_Atom_Exception('id is already set');
		} else {
			$this->id = $text;
		}
		$id_element = $this->addElement('id',$text);
	}

	function addLink($href,$rel='',$type='',$length='',$title='') 
	{
		$link = $this->addElement('link');
		$link->setAttribute('href',$href);
		if ($rel) {
			$link->setAttribute('rel',$rel);
		}
		if ($type) {
			$link->setAttribute('type',$type);
		}
		if ($length) {
			$link->setAttribute('length',$length);
		}
		if ($title) {
			$link->setAttribute('title',$title);
		}
		return $link;
	}

	function getNext() 
	{
		return $this->getLink('next');
	}

	function getPrevious() 
	{
		return $this->getLink('previous');
	}

	function getLink($rel='alternate',$title='') 
	{

		foreach ($this->root->getElementsByTagNameNS(Dase_Atom::$ns['atom'],'link') as $el) {
			//allow filtering on title
			if ($title) {
				if ($rel == $el->getAttribute('rel') && $title == $el->getAttribute('title')) {
					return $el->getAttribute('href');
				}
			} else {
				if ($rel == $el->getAttribute('rel')) {
					return $el->getAttribute('href');
				}
			}
		}
	}

	//belongs in Atom/Entry.php ??
	function getEnclosure() 
	{
		foreach ($this->root->getElementsByTagNameNS(Dase_Atom::$ns['atom'],'link') as $el) {
			if ('enclosure' == $el->getAttribute('rel')) {
				$enc['href'] = $el->getAttribute('href');
				$enc['mime_type'] = $el->getAttribute('type');
				$enc['length'] = $el->getAttribute('length');
				return $enc;
			}
		}
	}

	function setRights($text) 
	{
		if ($this->rights_is_set) {
			throw new Dase_Atom_Exception('rights is already set');
		} else {
			$this->rights_is_set = true;
		}
		$rights = $this->addElement('rights',$text);
	}

	function getRights() 
	{
		return $this->getAtomElementText('rights');
	}

	function setTitle($text) 
	{
		if ($this->title_is_set) {
			throw new Dase_Atom_Exception('title is already set');
		} else {
			$this->title_is_set = true;
		}
		$title = $this->addElement('title',$text);
	}

	function getAtomElementText($name,$ns_prefix='atom') 
	{
		//only works w/ simple string
		if ($this->root->getElementsByTagNameNS(Dase_Atom::$ns[$ns_prefix],$name)->item(0)) {
			return trim($this->root->getElementsByTagNameNS(Dase_Atom::$ns[$ns_prefix],$name)->item(0)->nodeValue);
		}
	}

	function getXpathValue($xpath) 
	{
		if ('DOMDocument' != get_class($this->dom)) {
			$c = get_class($this->dom);
			throw new Dase_Atom_Exception("xpath must be performed on DOMDocument, not $c");
		}
		$x = new DomXPath($this->dom);
		foreach (Dase_Atom::$ns as $k => $v) {
			$x->registerNamespace($k,$v);
		}
		$it = $x->query($xpath)->item(0);
		if ($it) {
			return $it->nodeValue;
		}
	}

	function getTitle() 
	{
		return $this->getAtomElementText('title');
	}

	function getUpdated() 
	{
		return $this->getAtomElementText('content');
	}

	function setUpdated($text) 
	{
		if ($this->updated_is_set) {
			throw new Dase_Atom_Exception('updated is already set');
		} else {
			$this->updated_is_set = true;
		}
		$updated = $this->addElement('updated',$text);
	}

	function asXml() 
	{
		if (!$this->id) {
			$today = date("Y-m-d"); 
			$this->setId('tag:daseproject.org,'.$today.':'.time());
		}
		if (!$this->updated_is_set) {
			$this->setUpdated(date(DATE_ATOM));
		}
		//format output
		$this->dom->formatOutput = true;
		return $this->dom->saveXML();
	}

	function getName()
	{
		//how we generally model names in DASe
		//nb. may be confused w/ author/name
		return $this->getTitle();
	}

	function getAsciiId()
	{
		//by convention, for entities that have an ascii id,
		//it will be the last segment of the atom:id
		return array_pop(explode('/',$this->getId()));
	}
}
