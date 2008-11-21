<?php

/*** a minimal atom entry
 
<?xml version="1.0" encoding="utf-8"?>
<entry xmlns="http://www.w3.org/2005/Atom">
  <id>tag:daseproject.org,2008:temp</id>
  <author><name/></author>
  <title>title</title>
  <updated>2008-01-01T00:00:00Z</updated>
  <link href="http://daseproject.org/atom/entry/template.html"/>
</entry>

*********/

class Dase_Atom_Entry extends Dase_Atom
{
	protected $edited_is_set;
	protected $content_is_set;
	protected $published_is_set;
	protected $source_is_set;
	protected $summary_is_set;
	protected $entrytype;
	public static $types_map = array(
		'attribute' => 'Dase_Atom_Entry_Attribute',
		'collection' => 'Dase_Atom_Entry_Collection',
		'comment' => 'Dase_Atom_Entry_Comment',
		'item' => 'Dase_Atom_Entry_Item',
		'set' => 'Dase_Atom_Entry_Set',
	);

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

	public static function retrieve($url,$user='',$pwd='') 
	{
		Dase_Log::debug('retrieving atom entry: '.$url);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);

		//do not need to verify certificate
		//from http://blog.taragana.com/index.php/archive/how-to-use-curl-in-php-for-authentication-and-ssl-communication/
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

		//this will NOT work in safemode
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION,1);
		if ($user && $pwd) {
			curl_setopt($ch, CURLOPT_USERPWD,"$user:$pwd");
		}
		$xml = curl_exec($ch);
		$info = curl_getinfo($ch);
		curl_close($ch);
		if ('200' == $info['http_code']) {
			return self::load($xml);
		} else {
			return $info['http_code'];
		}
	}

	public static function load($xml) 
	{
		//reader object
		$dom = new DOMDocument('1.0','utf-8');
		if (is_file($xml)) {
			$dom->load($xml);
		} else {
			$dom->loadXml($xml);
		}
		$entry = $dom->getElementsByTagNameNS(Dase_Atom::$ns['atom'],'entry');
		$root = $entry->item(0);
		foreach ($dom->getElementsByTagNameNS(Dase_Atom::$ns['atom'],'category') as $el) {
			if ('http://daseproject.org/category/entrytype' == $el->getAttribute('scheme')) {
				$entrytype = $el->getAttribute('term');
				$class = self::$types_map[$entrytype];
				if ($class) {
					$obj = new $class($dom,$root);
					$obj->entrytype = $entrytype;
					return $obj;
				} else {
					$entry = new Dase_Atom_Entry($dom,$root);
					$entry->entrytype = 'none';
					return $entry;
				}
			}
		}
		//in case no category element
		$entry = new Dase_Atom_Entry($dom);
		$entry->entrytype = 'none';
		return $entry;
	}

	public function putToUrl($url,$user,$pwd)
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
		curl_setopt($ch, CURLOPT_POSTFIELDS, $this->asXml());
		curl_setopt($ch, CURLOPT_USERPWD,$user.':'.$pwd);
		$str  = array(
			"Content-Type: application/atom+xml;type=entry"
		);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $str);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$result = curl_exec($ch);
		Dase_Log::debug($result);
		$info = curl_getinfo($ch);
		curl_close($ch);  
		if ('200' == $info['http_code']) {
			return 'ok';
		} else {
			return $result;
		}
	}



	function __get($var) 
	{
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

	function setMediaContent($url,$mime) 
	{
		if ($this->content_is_set) {
			throw new Dase_Atom_Exception('content is already set');
		} else {
			$this->content_is_set = true;
		}
		$content = $this->addElement('content');
		$content->setAttribute('src',$url);
		$content->setAttribute('type',$mime);
	}

	function getGoogleMetadata() {
		$metadata = array();
		foreach ($this->root->getElementsByTagNameNS(Dase_Atom::$ns['gsx'],'*') as $dd) {
			if ('admin_' != substr($dd->localName,0,6)) {
				$metadata[$dd->localName]['attribute_name'] = $dd->getAttributeNS(Dase_Atom::$ns['d'],'label');
				$metadata[$dd->localName]['values'][] = $dd->nodeValue;
			}
		}
		return $metadata;
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

	function getSummary() 
	{
		return $this->getAtomElementText('summary');
	}

	function getSummaryType() 
	{
		foreach ($this->root->getElementsByTagNameNS(Dase_Atom::$ns['atom'],'summary') as $el) {
			return $el->getAttribute('type');
		}
	}

	function getContent() 
	{
		return $this->getAtomElementText('content');
	}

	function getContentType() 
	{
		foreach ($this->root->getElementsByTagNameNS(Dase_Atom::$ns['atom'],'content') as $el) {
			return $el->getAttribute('type');
		}
	}

	function getId() 
	{
		return $this->getAtomElementText('id');
	}

	function setEntryType($type) 
	{
		$this->addCategory($type,'http://daseproject.org/category/entrytype'); 
	}

	function getEntryType() 
	{
		foreach ($this->root->getElementsByTagNameNS(Dase_Atom::$ns['atom'],'category') as $el) {
			if ('http://daseproject.org/category/entrytype' == $el->getAttribute('scheme')) {
				return $el->getAttribute('term');
			}
		}
	}

	function setEdited($dateTime)
	{
		if ($this->edited_is_set) {
			throw new Dase_Atom_Exception('edited is already set');
		} else {
			$this->edited_is_set = true;
		}
		$edited = $this->addElement('app:edited',$dateTime,Dase_Atom::$ns['app']);
	}

	function getAuthorName()
	{
		return $this->getXpathValue("atom:author/atom:name");
	}

	function getCategories() {
		$categories = array();
		foreach ($this->root->getElementsByTagNameNS(Dase_Atom::$ns['atom'],'category') as $cat) {
			$category['term'] = $cat->getAttribute('term');
			$category['label'] = $cat->getAttribute('label');
			$category['scheme'] = $cat->getAttribute('scheme');
			$categories[] = $category;
		}
		return $categories;
	}
}
