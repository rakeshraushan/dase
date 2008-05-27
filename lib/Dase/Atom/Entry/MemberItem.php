<?php
class Dase_Atom_Entry_MemberItem extends Dase_Atom_Entry_Item
{
	protected $edited_is_set;
	protected $app = "http://www.w3.org/2007/app";

	function __construct($dom=null,$root=null)
	{
		parent::__construct($dom,$root);
	}

	//because we do not yet have late static binding
	public static function load($xml_file) {
		$xml = file_get_contents($xml_file);
		$dom = new DOMDocument('1.0','utf-8');
		$dom->loadXML($xml);
		$entry = $dom->getElementsByTagNameNS(Dase_Atom::$ns['atom'],'entry');
		$root = $entry->item(0);
		return new Dase_Atom_Entry_MemberItem($dom,$root);
	}

	function insert($request) 
	{
		$c = Dase_DBO_Collection::get($request->get('collection_ascii_id'));
		$item = Dase_DBO_Item::create($c->ascii_id);
		foreach ($this->metadata as $att => $keyval) {
			foreach ($keyval['values'] as $v) {
				$item->setValue($att,$v);
			}
		}
		$item->buildSearchIndex();
		return $item;
	}

	function replace($request) 
	{
		$item = Dase_DBO_Item::get($request->get('collection_ascii_id'),$request->get('serial_number'));
		if ($item) {
		$item->deleteValues();
		foreach ($this->metadata as $att => $keyval) {
			foreach ($keyval['values'] as $v) {
				$item->setValue($att,$v);
			}
		}
		$item->buildSearchIndex();
		return $item;
		} else {
			Dase::error(404);
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

	function setEdited($dateTime)
	{
		if ($this->edited_is_set) {
			throw new Dase_Atom_Exception('edited is already set');
		} else {
			$this->edited_is_set = true;
		}
		$edited = $this->addElement('app:edited',$dateTime,$this->app);
	}

}
