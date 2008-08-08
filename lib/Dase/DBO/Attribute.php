<?php

require_once 'Dase/DBO/Autogen/Attribute.php';

class Dase_DBO_Attribute extends Dase_DBO_Autogen_Attribute
{
	public $cardinality;
	public $is_identifier;
	public $collection = null;
	public $display_values = array();

	const INPUT_TEXT = 'text';
	const INPUT_TEXTAREA = 'textarea';
	const INPUT_RADIO = 'radio';
	const INPUT_CHECKBOX = 'checkbox';
	const INPUT_SELECT = 'select';
	const INPUT_LISTBOX = 'listbox';
	const INPUT_NOEDIT = 'no_edit';
	const INPUT_DYNAMIC = 'text_with_menu';

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

	public static function findOrCreate($collection_ascii_id,$attribute_ascii_id) 
	{
		$att = new Dase_DBO_Attribute;
		$att->collection_id = Dase_DBO_Collection::get($collection_ascii_id)->id;
		$att->ascii_id = $attribute_ascii_id;
		if (!$att->findOne()) {
			$att->attribute_name = ucwords(str_replace('_',' ',$attribute_ascii_id));
			$att->sort_order = 9999;
			$att->in_basic_search = 1;
			$att->is_on_list_display = 1;
			$att->is_public = 1;
			$att->mapped_admin_att_id = 0;
			$att->updated = date(DATE_ATOM);
			$att->html_input_type = 'text';
			$att->insert();
		}
		return $att;
	}

	public static function findOrCreateAdmin($attribute_ascii_id) 
	{
		$att = new Dase_DBO_Attribute;
		$att->collection_id = 0;
		$att->ascii_id = $attribute_ascii_id;
		if (!$att->findOne()) {
			$att->attribute_name = ucwords(str_replace('_',' ',$attribute_ascii_id));
			$att->sort_order = 0;
			$att->in_basic_search = 0;
			$att->is_on_list_display = 0;
			$att->is_public = 0;
			$att->mapped_admin_att_id = 0;
			$att->updated = date(DATE_ATOM);
			$att->html_input_type = 'text';
			$att->insert();
		}
		return $att;
	}

	function injectAtomEntryData(Dase_Atom_Entry $entry)
	{
		$collection = $this->getCollection();
		$entry->setTitle($this->attribute_name);
		$entry->setId(APP_ROOT.'/attribute/'.$collection->ascii_id.'/'.$this->ascii_id);
		$entry->addCategory('attribute','http://daseproject.org/category/entrytype','Attribute');
		if (is_numeric($this->updated)) {
			$updated = date(DATE_ATOM,$this->updated);
		} else {
			$updated = $this->updated;
		}
		$entry->setUpdated($updated);
		$entry->addAuthor('ss');
		/*
		$div = simplexml_import_dom($entry->setContent());

		$dl = $div->addChild('dl');
		foreach ($this as $k => $v) {
			$dt = $dl->addChild('dt',$k);
			$dd = $dl->addChild('dd',$v);
			$dd->addAttribute('class',$k);
		}
		 */
		return $entry;
	}

	function getDisplayValues($coll = null,$limit=1000)
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
		if ($limit) {
			$limit_sql = "LIMIT $limit";
		}
		$sql = "
			SELECT value_text, count(value_text)
			FROM value
			WHERE attribute_id = ?
			$admin_sql
			GROUP BY value_text
			ORDER BY value_text
			$limit_sql;
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

	public function getFormValues() 
	{
		//todo: this could use some optimization
		$values = array();
		if (in_array($this->html_input_type,array('radio','checkbox','select'))) {
			$dv = new Dase_DBO_DefinedValue();
			$dv->attribute_id = $this->id;
			foreach ($dv->find() as $defval) {
				$values[] = $defval->value_text;
			}
		} elseif ('text_with_menu' == $this->html_input_type) {
			$v = new Dase_DBO_Value;
			$v->attribute_id = $this->id;
			foreach ($v->find() as $value) {
				$values[] = $value->value_text;
			}
			$values = array_unique($values);
			asort($values);
		} else {
			//nothin'
		}
		return $values;
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
		//avoids another db lookup
		if ($this->collection) {
			return $this->collection;
		}
		$c = new Dase_DBO_Collection;
		$c->load($this->collection_id);
		$this->collection = $c;
		return $c;
	}

	function getDefinedValues() {
		$defined = array();
		$dvs = new Dase_DBO_DefinedValue;
		$dvs->attribute_id = $this->id;
		foreach ($dvs->find() as $dv) {
			$defined[] = $dv->value_text;
		}
		return $defined;
	}

	function getCurrentValues() {
		$current = array();
		$vals = new Dase_DBO_Value;
		$vals->attribute_id = $this->id;
		foreach ($vals->find() as $val) {
			$current[] = $val->value_text;
		}
		return $current;
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

	public function asArray() {
		$att_array = array();
		foreach ($this as $k => $v) {
			$att_array[$k] = $v;
		}
		$att_array['values'] = $this->getFormValues();
		return $att_array;
	}

	public function asJson() {
		return Dase_Json::get($this->asArray());
	}

	public function valuesAsAtom($collection_ascii_id)
	{
		if (0 == $this->collection_id) {
			//since it is admin att we need to be able to limit to items in this coll
			$values_array = $this->getDisplayValues($collection_ascii_id);
		} else {
			$values_array = $this->getDisplayValues();
		}
		$feed = new Dase_Atom_Feed;
		$feed->setId(APP_ROOT.'/attribute/'.$collection_ascii_id.'/'.$this->ascii_id.'/values');
		$feed->setFeedType('attribute_values');
		$feed->setTitle('values for '.$collection_ascii_id.'.'.$this->ascii_id);
		$feed->setUpdated(date(DATE_ATOM));
		$feed->addAuthor();
		$feed->addLink(APP_ROOT.'/attribute/'.$collection_ascii_id.'/'.$this->ascii_id.'/values.atom','self');
		foreach ($values_array as $v) {
			$entry = $feed->addEntry();
			$entry->setId(APP_ROOT.'/'.$collection_ascii_id.'/'.$this->ascii_id.'/'.$v['v']);
			$entry->addLink(APP_ROOT.'/search?'.$collection_ascii_id.'.'.$this->ascii_id.'='.$v['v']);
			$entry->setUpdated(date(DATE_ATOM));
			$entry->setTitle($v['v']);
		}
		return $feed->asXml();
	}

	public function fixBools()
	{
		//the purpose here is to prepare for update
		if (!$this->is_public) {
			$this->is_public = 0;
		}
		if (!$this->is_on_list_display) {
			$this->is_on_list_display = 0;
		}
		if (!$this->in_basic_search) {
			$this->in_basic_search = 0;
		}
	}
}


