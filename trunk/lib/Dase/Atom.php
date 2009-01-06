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
		if (!$ns) {
			$ns = Dase_Atom::$ns['atom'];
		}
		$elem = $this->root->appendChild($this->dom->createElementNS($ns,$tagname));
		if ($text || '0' === (string) $text) { //so '0' works
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

	function addCategory($term,$scheme='',$label='',$text='') 
	{
		$cat = $this->addElement('category',$text);
		$cat->setAttribute('term',$term);
		if ($scheme) {
			$cat->setAttribute('scheme',$scheme);
		}
		if ($label) {
			$cat->setAttribute('label',$label);
		}
		return $cat;
	}

	function getCategories() {
		$categories = array();
		foreach ($this->root->getElementsByTagNameNS(Dase_Atom::$ns['atom'],'category') as $cat) {
			$category = array();
			$category['term'] = $cat->getAttribute('term');
			$category['scheme'] = $cat->getAttribute('scheme');
			if ($cat->getAttribute('label')) {
				$category['label'] = $cat->getAttribute('label');
			}
			if ($cat->nodeValue || '0' === $cat->nodeValue) {
				$category['value'] = $cat->nodeValue;
			}
			$categories[] = $category;
		}
		return $categories;
	}

	function getCategoriesByScheme($scheme) {
		$categories = array();
		foreach ($this->root->getElementsByTagNameNS(Dase_Atom::$ns['atom'],'category') as $cat) {
			if ($scheme == $cat->getAttribute('scheme')) {
				$category['term'] = $cat->getAttribute('term');
				if ($cat->getAttribute('label')) {
					$category['label'] = $cat->getAttribute('label');
				}
				if ($cat->nodeValue || '0' === $cat->nodeValue) {
					$category['value'] = $cat->nodeValue;
				}
				$categories[] = $category;
			}
		}
		return $categories;
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

	function setId($text='') 
	{
		if ($this->id) {
			throw new Dase_Atom_Exception('id is already set');
		} elseif(!$text) {
			$text = 'tag:daseproject.org,'.date("Y-m-d").':'.time();
		} 
		$this->id = $text;
		$id_element = $this->addElement('id',$text);
	}

	function addLink($href,$rel='',$type='',$length='',$title='') 
	{
		$link = $this->addElement('link');
		//a felicitous attribute order
		if ($rel) {
			$link->setAttribute('rel',$rel);
		}
		if ($title) {
			$link->setAttribute('title',$title);
		}
		$link->setAttribute('href',$href);
		if ($type) {
			$link->setAttribute('type',$type);
		}
		if ($length) {
			$link->setAttribute('length',$length);
		}
		return $link;
	}

	function getLinks() {
		$links = array();
		foreach ($this->root->getElementsByTagNameNS(Dase_Atom::$ns['atom'],'link') as $ln) {
			$link['rel'] = $ln->getAttribute('rel');
			$link['href'] = $ln->getAttribute('href');
			$link['title'] = $ln->getAttribute('title');
			$link['type'] = $ln->getAttribute('type');
			$link['length'] = $ln->getAttribute('length');
			foreach ($link as $k => $v) {
				if (!$link[$k] && '0' !== $link[$k]) {
					unset ($link[$k]);
				}
			}
			$links[] = $link;
		}
		return $links;
	}

	function getNext() 
	{
		return $this->getLink('next');
	}

	function getPrevious() 
	{
		return $this->getLink('previous');
	}

	function getServiceLink()
	{
		return $this->getLink('service');
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

	function getRelatedLinks() 
	{
		$links = array();
		foreach ($this->root->getElementsByTagNameNS(Dase_Atom::$ns['atom'],'link') as $el) {
			//title is required!
			if ('related' == $el->getAttribute('rel') && $el->getAttribute('title')) {
				$links[$el->getAttribute('href')]['title'] = $el->getAttribute('title');
				$links[$el->getAttribute('href')]['count'] = $el->getAttributeNS(Dase_Atom::$ns['thr'],'count');
			}
		}
		return $links;
	}

	function getParentLinks() 
	{
		$links = array();
		foreach ($this->root->getElementsByTagNameNS(Dase_Atom::$ns['atom'],'link') as $el) {
			//title is required!
			if ('http://daseproject.org/relation/parent' == $el->getAttribute('rel') && $el->getAttribute('title')) {
				$links[$el->getAttribute('href')]['title'] = $el->getAttribute('title');
			}
		}
		return $links;
	}

	function getChildFeedLinks() 
	{
		$links = array();
		foreach ($this->root->getElementsByTagNameNS(Dase_Atom::$ns['atom'],'link') as $el) {
			//title is required!
			if ('http://daseproject.org/relation/childfeed' == $el->getAttribute('rel') && $el->getAttribute('title') && ('application/atom+xml' == $el->getAttribute('type'))) {
				$links[$el->getAttribute('href')]['title'] = $el->getAttribute('title');
				$links[$el->getAttribute('href')]['count'] = $el->getAttributeNS(Dase_Atom::$ns['thr'],'count');
			}
		}
		return $links;
	}

	function getChildJsonFeedLinks() 
	{
		$links = array();
		foreach ($this->root->getElementsByTagNameNS(Dase_Atom::$ns['atom'],'link') as $el) {
			//title is required!
			if ('http://daseproject.org/relation/childfeed' == $el->getAttribute('rel') && $el->getAttribute('title') && ('application/json' == $el->getAttribute('type'))) {
				$links[$el->getAttribute('href')]['title'] = $el->getAttribute('title');
				$links[$el->getAttribute('href')]['count'] = $el->getAttributeNS(Dase_Atom::$ns['thr'],'count');
			}
		}
		return $links;
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

	function getTitle() 
	{
		return $this->getAtomElementText('title');
	}

	function getAtomElementText($name,$ns_prefix='atom') 
	{
		//only works w/ simple string
		if ($this->root->getElementsByTagNameNS(Dase_Atom::$ns[$ns_prefix],$name)->item(0)) {
			return trim($this->root->getElementsByTagNameNS(Dase_Atom::$ns[$ns_prefix],$name)->item(0)->nodeValue);
		}
	}

	function getXpathValue($xpath,$context_node = null) 
	{
		if ('DOMDocument' != get_class($this->dom)) {
			$c = get_class($this->dom);
			throw new Dase_Atom_Exception("xpath must be performed on DOMDocument, not $c");
		}
		$x = new DomXPath($this->dom);
		foreach (Dase_Atom::$ns as $k => $v) {
			$x->registerNamespace($k,$v);
		}
		if ($context_node) {
			$it = $x->query($xpath,$context_node)->item(0);
		} else {
			$it = $x->query($xpath)->item(0);
		}
		if ($it) {
			return $it->nodeValue;
		}
	}

	function getUpdated() 
	{
		return $this->getAtomElementText('updated');
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
		//format output
		$this->dom->formatOutput = true;
		return $this->dom->saveXML();
	}

	function getAsciiId()
	{
		//by convention, for entities that have an ascii id,
		//it will be the last segment of the atom:id
		return array_pop(explode('/',$this->getId()));
	}
}
