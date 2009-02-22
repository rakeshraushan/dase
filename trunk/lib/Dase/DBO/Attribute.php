<?php

require_once 'Dase/DBO/Autogen/Attribute.php';

class Dase_DBO_Attribute extends Dase_DBO_Autogen_Attribute
{
	public $collection = null;
	public $value;
	public $form_values = array();
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

	public static function get($db,$collection_ascii_id,$ascii_id)
	{
		if ($collection_ascii_id && $ascii_id) {
			$a = new Dase_DBO_Attribute($db);
			$a->ascii_id = $ascii_id;
			if ('admin_' == substr($ascii_id,0,6)) {
				$a->collection_id = 0;
			} else {
				$a->collection_id = Dase_DBO_Collection::get($db,$collection_ascii_id)->id;
			}
			return($a->findOne());
		} else {
			throw new Exception('missing a method parameter value');
		}
	}

	public static function getAdmin($db,$ascii_id)
	{
		if ($ascii_id) {
			$a = new Dase_DBO_Attribute($db);
			$a->ascii_id = $ascii_id;
			$a->collection_id = 0;
			return($a->findOne());
		} else {
			throw new Exception('missing method parameter');
		}
	}

	public static function findOrCreate($db,$collection_ascii_id,$attribute_ascii_id) 
	{
		$att = new Dase_DBO_Attribute($db);
		$att->collection_id = Dase_DBO_Collection::get($db,$collection_ascii_id)->id;
		$att->ascii_id = $attribute_ascii_id;
		if (!$att->findOne()) {
			$att->attribute_name = ucwords(str_replace('_',' ',strtolower($attribute_ascii_id)));
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

	public static function findOrCreateAdmin($db,$attribute_ascii_id) 
	{
		$att = new Dase_DBO_Attribute($db);
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

	function asAtomEntry($collection_ascii_id,$app_root)
	{
		$entry = new Dase_Atom_Entry;
		return $this->injectAtomEntryData($entry,$collection_ascii_id,$app_root)->asXml();
	}

	public function getUrl($collection_ascii_id,$app_root)
	{
		if (false !== strpos($this->ascii_id,'admin_')) {
			return $app_root.'/attribute/'.$this->ascii_id;
		}
		return $app_root.'/attribute/'.$collection_ascii_id.'/'.$this->ascii_id;
	}

	function injectAtomEntryData(Dase_Atom_Entry $entry,$collection_ascii_id,$app_root)
	{
		$url = $this->getUrl($collection_ascii_id,$app_root);
		$entry->setTitle($this->attribute_name);
		$entry->setId($url);
		$entry->addLink($url.'.atom');
		$entry->addLink($url.'.atom','edit');
		$entry->addCategory('attribute','http://daseproject.org/category/entrytype');
		$entry->addCategory($this->html_input_type,'http://daseproject.org/category/html_input_type');
		foreach ($this->getItemTypes() as $type) {
			$entry->addCategory($type->ascii_id,'http://daseproject.org/category/parent_item_type',$type->name);
		}
		if (in_array($this->html_input_type,array('checkbox','select','radio'))) {
			$entry->addLink($url.'/defined','http://daseproject.org/relation/defined_values','application/atomcat+xml');
		}
		//compat
		if (is_numeric($this->updated)) {
			$updated = date(DATE_ATOM,$this->updated);
		} else {
			$updated = $this->updated;
		}
		$entry->setUpdated($updated);
		$entry->addAuthor();
		return $entry;
	}

	function getDisplayValues($coll = null,$limit=2000,$filter_key='',$filter_value='')
	{
		$prefix = $this->db->table_prefix;
		$dbh = $this->db->getDbh();
		$admin_sql = '';
		$filter_sql = '';
		if (!$this->id) {
			throw new Exception('attribute not instantiated/loaded'); 
		}
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
		$st = $dbh->prepare($sql);
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
			$dv = new Dase_DBO_DefinedValue($this->db);
			$dv->attribute_id = $this->id;
			foreach ($dv->find() as $defval) {
				$values[] = $defval->value_text;
			}
		} elseif ('text_with_menu' == $this->html_input_type) {
			$v = new Dase_DBO_Value($this->db);
			$v->attribute_id = $this->id;
			foreach ($v->find() as $value) {
				$values[] = $value->value_text;
			}
			$values = array_values(array_unique($values));
			asort($values);
		} else {
			//nothin'
		}
		$this->form_values = $values;
		return $values;
	}

	public static function listAdminAttIds($db)
	{
		$ids = array();
		$a = new Dase_DBO_Attribute($db);
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
		$c = new Dase_DBO_Collection($this->db);
		$c->load($this->collection_id);
		$this->collection = $c;
		return $c;
	}

	function getDefinedValues() {
		$defined = array();
		$dvs = new Dase_DBO_DefinedValue($this->db);
		$dvs->attribute_id = $this->id;
		foreach ($dvs->find() as $dv) {
			$defined[] = $dv->value_text;
		}
		return $defined;
	}

	function addDefinedValue($text) {
		$dv = new Dase_DBO_DefinedValue($this->db);
		$dv->attribute_id = $this->id;
		$dv->value_text = $text;
		$dv->insert();
	}

	function getCurrentValues() {
		$current = array();
		$vals = new Dase_DBO_Value($this->db);
		$vals->attribute_id = $this->id;
		foreach ($vals->find() as $val) {
			$current[] = $val->value_text;
		}
		return $current;
	}

	function getItemTypes()
	{
		$item_types = array();
		$att_it = new Dase_DBO_AttributeItemType($this->db);
		$att_it->attribute_id = $this->id;
		foreach($att_it->find() as $ait) {
			$it = new Dase_DBO_ItemType($this->db);
			$it->load($ait->item_type_id);
			$item_types[] = $it;
		}
		$this->item_types = $item_types;
		return $item_types;
	}

	function addItemType($item_type_ascii)
	{
		$c = $this->getCollection();
		$type = Dase_DBO_ItemType::get($this->db,$c->ascii_id,$item_type_ascii);
		if ($type) {
			$ita = new Dase_DBO_AttributeItemType($this->db);
			$ita->attribute_id = $this->id;
			$ita->item_type_id = $type->id;
			if (!$ita->findOne()) {
				$ita->insert();
			}
		}
	}

	function expunge() {
		$dv = new Dase_DBO_DefinedValue($this->db);
		$dv->attribute_id = $this->id;
		foreach ($dv->find() as $doomed) {
			$doomed->delete();
		}

		$ait = new Dase_DBO_AttributeItemType($this->db);
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
		$aa = new Dase_DBO_Attribute($this->db);
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

	public function valuesAsAtom($collection_ascii_id,$filter_key ='',$filter_value='',$app_root)
	{
		if (0 == $this->collection_id) {
			//since it is admin att we need to be able to limit to items in this coll
			$values_array = $this->getDisplayValues($collection_ascii_id,2000,$filter_key,$filter_value);
		} else {
			$values_array = $this->getDisplayValues(null,2000,$filter_key,$filter_value);
		}
		$feed = new Dase_Atom_Feed;
		$feed->setId($app_root.'/attribute/'.$collection_ascii_id.'/'.$this->ascii_id.'/values');
		$feed->setFeedType('attribute_values');
		$feed->setTitle('values for '.$collection_ascii_id.'.'.$this->ascii_id);
		//since we do not have a class for an attribute_value feed, stick ascii_id in subtitle
		$feed->setSubtitle($this->ascii_id);
		$feed->setUpdated(date(DATE_ATOM));
		$feed->addAuthor();
		$feed->addLink($app_root.'/attribute/'.$collection_ascii_id.'/'.$this->ascii_id.'/values.atom','self');
		foreach ($values_array as $v) {
			$entry = $feed->addEntry();
			$entry->setId($app_root.'/'.$collection_ascii_id.'/'.$this->ascii_id.'/'.$v['v']);
			$entry->addLink($app_root.'/search?'.$collection_ascii_id.'.'.$this->ascii_id.'='.$v['v']);
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

	public function resort($target = 0) 
	{
		$seen  = false;
		$new_sort_order = 0;
		$coll = $this->getCollection(); 
		foreach ($coll->getAttributes() as $att) {
			//$this->log->debug('----------------'.$target);
			$new_sort_order++;
			if ($new_sort_order == $target) {
				$this->sort_order = $new_sort_order;
				$this->fixBools();
				$this->update();
				$seen = true;
			} 
			if ($target && $att->ascii_id == $this->ascii_id) {
				//skip
			} else {
				if ($seen) {
					$new_sort_order++;
				}
				$att->sort_order = $new_sort_order;
				$att->fixBools();
				$att->update();
			}
		}
		if ($target && !$seen) { //meaning target is last or higher
			$this->sort_order = $new_sort_order;
			$this->fixBools();
			$this->update();
		}
	}
}


