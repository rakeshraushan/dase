<?php
class Dase_Atom_Feed extends Dase_Atom 
{
	public $dom;
	protected $_entries = array();
	protected $_entriesIndex = 0;
	public $root;
	protected $generator_is_set;
	protected $subtitle_is_set;
	private static $types_map = array(
		'collection_list' => array(
			'feed' => 'Dase_Atom_Feed_CollectionList', 
			'entry' => 'Dase_Atom_Entry_Collection'
		),
		'collection' => array(
			'feed' => 'Dase_Atom_Feed_Collection',
			'entry' => 'Dase_Atom_Entry_Collection',
		),
		'search_result' => array(
			'feed' => 'Dase_Atom_Feed_SearchResult',
			'entry' => 'Dase_Atom_Entry_Item',
		),
		'item' => array(
			'feed' => 'Dase_Atom_Feed_Item',
			'entry' => 'Dase_Atom_Entry_Item',
		),
	);
	protected $feedtype;

	function __construct($xml = null)
	{
		$dom = new DOMDocument('1.0','utf-8');
		$this->dom = $dom;
		if ($xml) {
			$this->dom->loadXML($xml);
			$this->root = $this->dom;
		} else {
			$this->root = $this->dom->appendChild($this->dom->createElementNS(Dase_Atom::$ns['atom'],'feed'));
		}
	}

	public static function retrieve($url) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		$xml = curl_exec($ch);
		curl_close($ch);
		$dom = new DOMDocument('1.0','utf-8');
		$dom->loadXML($xml);
		foreach ($dom->getElementsByTagNameNS(Dase_Atom::$ns['atom'],'category') as $el) {
			if ('http://daseproject.org/category/feedtype' == $el->getAttribute('scheme')) {
				$feedtype = $el->getAttribute('term');
				$class = self::$types_map[$feedtype]['feed'];
				if ($class) {
					$obj = new $class($xml);
					$obj->feedtype = $feedtype;
					return $obj;
				} else {
					$feed = new Dase_Atom_Feed($xml);
					$feed->feedtype = 'none';
					return $feed;
				}
			}
		}
	}

	function setFeedType($type) 
	{
		$this->addCategory($type,'http://daseproject.org/category/feedtype'); 
	}

	function getFeedType($type) 
	{
		foreach ($this->dom->getElementsByTagNameNS(Dase_Atom::$ns['atom'],'category') as $el) {
			if ('http://daseproject.org/category/feedtype' == $el->getAttribute('scheme')) {
				return $el->getAttribute('term');
			}
		}
	}

	function addEntry()
	{
		$entry = new Dase_Atom_Entry($this->dom);
		$this->_entries[] = $entry;
		return $entry;
	}

	function setGenerator($text,$uri='',$version='')
	{
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

	function setSubtitle($text='')
	{
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
			//return $this->addChildElement($subtitle,'xhtml:div','',Dase_Atom::$ns['h']);
			$div = $subtitle->appendChild($this->dom->createElement('div'));
			$div->setAttribute('xmlns',Dase_Atom::$ns['h']);
			return $div;
			//note that best practice here is to use simplexml 
			//to add subtitle to the returned div
		}
	}

	function setOpensearchTotalResults($num)
	{
		$this->addElement('totalResults',$num,Dase_Atom::$ns['opensearch']);
	}

	function setOpensearchStartIndex($num)
	{
		$this->addElement('startIndex',$num,Dase_Atom::$ns['opensearch']);
	}

	function setOpensearchItemsPerPage($num)
	{
		$this->addElement('itemsPerPage',$num,Dase_Atom::$ns['opensearch']);
	}

	function asXml()
	{
		//attach entries
		if ($this->_entries) {
			foreach ($this->_entries as $entry) {
				$this->root->appendChild($entry->root);
			}
		}
		return parent::asXml();
	}

	protected function getEntries()
	{
		$class = self::$types_map[$this->feedtype]['entry'];
		foreach ($this->dom->getElementsByTagNameNS(Dase_Atom::$ns['atom'],'entry') as $entry_dom) {
			if ($class) {
				//entry subclass
				$entry = new $class($entry_dom,$this->dom);
			} else {
				$entry = new Dase_Atom_Entry($entry_dom,false,$this->dom);
			}
			$this->_entries[] = $entry;
		}
		return $this->_entries;
	}

	protected function getSubtitle() {
		return $this->getAtomElementText('subtitle');
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
}
