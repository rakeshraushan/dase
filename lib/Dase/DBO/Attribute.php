<?php

require_once 'Dase/DBO/Autogen/Attribute.php';

class Dase_DBO_Attribute extends Dase_DBO_Autogen_Attribute
{
	public $cardinality;
	public $is_identifier;
	public $collection = null;
	public $display_values = array();
	public $html_input_type = null;

	function getValueCount()
	{
		if (!$this->id) {
			throw new Exception('attribute not instantiated/loaded'); 
		}
		$db = Dase_DB::get();
		$st = $db->prepare('SELECT count(*) FROM value WHERE attribute_id = ?');
		$st->execute(array($this->id));	
		return $st->fetchColumn();
	}

	function injectAtomEntryData(Dase_Atom_Entry $entry,$collection)
	{
		$entry->setTitle('Attribute '.$this->attribute_name);
		$entry->setId(APP_ROOT.'/attribute/'.$collection->ascii_id.'/'.$this->ascii_id);
		$entry->addCategory($this->attribute_name,'http://daseproject.org/category/collection/attribute',$this->ascii_id);
		$entry->addCategory('attribute','http://daseproject.org/category','Attribute');
		if (is_numeric($this->updated)) {
			$updated = date(DATE_ATOM,$this->updated);
		} else {
			$updated = $this->updated;
		}
		$entry->setUpdated($updated);
		$entry->addAuthor('ss');
		$div = simplexml_import_dom($entry->setContent());

		$dl = $div->addChild('dl');
		foreach ($this as $k => $v) {
			$dt = $dl->addChild('dt',$k);
			$dd = $dl->addChild('dd',$v);
			$dd->addAttribute('class',$k);
		}
		return $entry;
	}

	function getDisplayValues($coll = null)
	{
		$admin_sql = '';
		if (!$this->id) {
			throw new Exception('attribute not instantiated/loaded'); 
		}
		$db = Dase_DB::get();
		//presence of collection_id says it is an admin att
		//todo: make sure $coll is a-z or '_'
		if ($coll) {
			$admin_sql = "AND item_id IN (SELECT id FROM item WHERE collection_id IN (SELECT id FROM collection WHERE ascii_id = '$coll'))";
		}
		$sql = "
			SELECT value_text, count(value_text)
			FROM value
			WHERE attribute_id = ?
			$admin_sql
			GROUP BY value_text
			ORDER BY value_text
			";
		$st = $db->prepare($sql);
		$st->execute(array($this->id));
		$display_values_array = array();
		while ($row = $st->fetch()) {
			$display_values_array[] = array(
				'v' => $row[0],
				't' => $row[1]
			);
		}
		$this->display_values = $display_values_array;
		return $display_values_array;
	}

	public static function get($collection_ascii_id,$ascii_id)
	{
		if ($collection_ascii_id && $ascii_id) {
			$a = new Dase_DBO_Attribute;
			$a->ascii_id = $ascii_id;
			if ('admin_' == substr($ascii_id,0,6)) {
				$a->collection_id = 0;
			} else {
				$a->collection_id = Dase_DBO_Collection::get($collection_ascii_id)->id;
			}
			return($a->findOne());
		}
	}

	public static function getAdmin($ascii_id)
	{
		$a = new Dase_DBO_Attribute;
		$a->ascii_id = $ascii_id;
		$a->collection_id = 0;
		return($a->findOne());
	}

	public function getCollection()
	{
		$c = new Dase_DBO_Collection;
		$c->load($this->collection_id);
		$this->collection = $c;
		return $c;
	}

	function getHtmlInputType()
	{
		$inp = new Dase_DBO_HtmlInputType;
		$inp->load($this->html_input_type_id);
		$this->html_input_type = $inp;
		return $inp;
	}

	function getAdminEquiv($mapped_id)
	{
		if ($this->mapped_admin_att_id) {
			$mapped_id = $this->mapped_admin_att_id;
		}
		$aa = new Dase_DBO_Attribute;
		if ($aa->load($mapped_id)) {
			return $aa->ascii_id;
		} else {
			return 'none';
		}
	}

	public static function getId($ascii_id)
	{
		$db = Dase_DB::get();
		$sth = $db->prepare("SELECT id from attribute WHERE ascii_id = ?");
		$sth->execute(array($ascii_id));
		return $sth->fetchColumn();
	}
}


