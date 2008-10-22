<?php

require_once 'Dase/DBO/Autogen/Attribute.php';

class Dase_DBO_Attribute extends Dase_DBO_Autogen_Attribute
{
	public $cardinality;
	public $is_identifier;
	public $collection = null;
	public $display_values = array();

	public static $types_map = array(
		'text' => array('label'=>'Text'),
		'textarea' => array('label'=>'Textarea'),
		'radio' => array('label'=>'Radio Button'),
		'checkbox' => array('label'=>'Checkbox'),
		'select' => array('label'=>'Select Menu'),
		'listbox' => array('label'=>'MultiValue Magic Box'),
		'no_edit' => array('label'=>'Non-editable'),
		'text_with_menu' => array('label'=>'Text w/ Dynamic Menu'),
	);

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
		} else {
			throw new Exception('missing a method parameter value');
		}
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

	function asAtomEntry()
	{
		$entry = new Dase_Atom_Entry;
		return $this->injectAtomEntryData($entry)->asXml();
	}

	function injectAtomEntryData(Dase_Atom_Entry $entry)
	{
		$collection = $this->getCollection();
		$entry->setTitle($this->attribute_name);
		$entry->setId(APP_ROOT.'/attribute/'.$collection->ascii_id.'/'.$this->ascii_id);
		$entry->addLink(APP_ROOT.'/attribute/'.$collection->ascii_id.'/'.$this->ascii_id);
		$entry->addLink(APP_ROOT.'/attribute/'.$collection->ascii_id.'/'.$this->ascii_id,'edit');
		$entry->addCategory('attribute','http://daseproject.org/category/entrytype','Attribute');
		if (is_numeric($this->updated)) {
			$updated = date(DATE_ATOM,$this->updated);
		} else {
			$updated = $this->updated;
		}
		$entry->setUpdated($updated);
		$entry->addAuthor();
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

	function getDisplayValues($coll = null,$limit=2000,$filter_key='',$filter_value='')
	{
		$prefix = Dase_Config::get('table_prefix');
		$admin_sql = '';
		$filter_sql = '';
		if (!$this->id) {
			throw new Exception('attribute not instantiated/loaded'); 
		}
		$db = Dase_DB::get();
		//presence of collection_id says it is an admin att
		//todo: make sure $coll is a-z or '_'
		if ($coll) {
			$admin_sql = "AND item_id IN (SELECT id FROM {$prefix}item WHERE collection_id IN (SELECT id FROM {$prefix}collection WHERE ascii_id = '$coll'))";
		}
		if ($filter_key && $filter_value) {
			$filter_sql = "AND item_id IN (SELECT item_id FROM {$prefix}value v,{$prefix}attribute a WHERE v.value_text='$filter_value' and a.ascii_id = '$filter_key' and v.attribute_id = a.id)";
		}
		if ($limit) {
			$limit_sql = "LIMIT $limit";
		}
		$sql = "
			SELECT value_text, count(value_text)
			FROM {$prefix}value
			WHERE attribute_id = ?
			$admin_sql
			$filter_sql
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

	public static function getAdmin($ascii_id)
	{
		$a = new Dase_DBO_Attribute;
		$a->ascii_id = $ascii_id;
		$a->collection_id = 0;
		return($a->findOne());
	}

	public static function listAdminAttIds()
	{
		$ids = array();
		$a = new Dase_DBO_Attribute;
		$a->collection_id = 0;
		foreach ($a->find() as $att) {
			$ids[] = $att->id;
		}
		return $ids;
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

	function addDefinedValue($text) {
		$dv = new Dase_DBO_DefinedValue;
		$dv->attribute_id = $this->id;
		$dv->value_text = $text;
		$dv->insert();
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

	function expunge() {
		$dv = new Dase_DBO_DefinedValue;
		$dv->attribute_id = $this->id;
		foreach ($dv->find() as $doomed) {
			$doomed->delete();
		}

		$ait = new Dase_DBO_AttributeItemType;
		$ait->attribute_id = $this->id;
		foreach ($ait->find() as $doomed) {
			$doomed->delete();
		}

		$this->delete();
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
		$c = $this->getCollection();
		$att_array = array();
		foreach ($this as $k => $v) {
			$att_array[$k] = $v;
		}
		$att_array['values'] = $this->getFormValues();
		$att_array['collection_ascii_id'] = $c->ascii_id;
		return $att_array;
	}

	public function asJson() {
		return Dase_Json::get($this->asArray());
	}

	public function valuesAsAtom($collection_ascii_id,$filter_key ='',$filter_value='')
	{
		if (0 == $this->collection_id) {
			//since it is admin att we need to be able to limit to items in this coll
			$values_array = $this->getDisplayValues($collection_ascii_id,2000,$filter_key,$filter_value);
		} else {
			$values_array = $this->getDisplayValues(null,2000,$filter_key,$filter_value);
		}
		$feed = new Dase_Atom_Feed;
		$feed->setId(APP_ROOT.'/attribute/'.$collection_ascii_id.'/'.$this->ascii_id.'/values');
		$feed->setFeedType('attribute_values');
		$feed->setTitle('values for '.$collection_ascii_id.'.'.$this->ascii_id);
		//since we do not have a class for an attribute_value feed, stick ascii_id in subtitle
		$feed->setSubtitle($this->ascii_id);
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

	public function resort($sort_after=null) {
		$coll = $this->getCollection(); 
		if (!$sort_after) {
			$new_sort_order = 0;
			foreach ($coll->getAttributes() as $att) {
				$new_sort_order++;
				$att->sort_order = $new_sort_order;
				$att->update();
			}
		} elseif ('_first' == $sort_after) {
			$this->sort_order = 0;
			$this->update();
			$new_sort_order = 0;
			foreach ($coll->getAttributes() as $att) {
				$new_sort_order++;
				$att->sort_order = $new_sort_order;
				$att->update();
			}
		} else {
			$after_att = Dase_DBO_Attribute::get($coll->ascii_id,$sort_after);
			$after_sort = $after_att->sort_order;

			if ($this->sort_order < $after_sort) {
				foreach ($coll->getAttributes() as $att) {
					if (($att->sort_order > $this->sort_order) && ($att->sort_order <= $after_sort)) {
						$att->sort_order = $att->sort_order - 1;
						$att->update();
					}
					else if ($att->sort_order == $this->sort_order) {
						$att->sort_order = $after_sort;
						$att->update();
					}
				}
			}
			if ($this->sort_order > $after_sort) {
				foreach ($coll->getAttributes() as $att) {
					if (($att->sort_order > $after_sort) && ($att->sort_order < $this->sort_order)) {
						$att->sort_order = $att->sort_order + 1;
						$att->update();
					}
					else if ($att->sort_order == $this->sort_order) {
						$att->sort_order = $after_sort + 1;
						$att->update();
					}
				}
			}
		}
	}
}


