<?php

require_once 'Dase/DBO/Autogen/Item.php';

class Dase_DBO_Item extends Dase_DBO_Autogen_Item 
{

	public $collection = null;
	public $item_type;
	public $media = array();
	public $values = array();

	public static function get($collection_ascii_id,$serial_number)
	{
		if (!$collection_ascii_id || !$serial_number) {
			throw new Exception('missing information');
		}
		$c = Dase_DBO_Collection::get($collection_ascii_id);
		if (!$c) {
			return false;
		}
		$item = new Dase_DBO_Item;
		$item->collection_id = $c->id;
		$item->serial_number = $serial_number;
		return $item->findOne();
	}

	public function saveAtom()
	{
		$atom = new Dase_DBO_ItemAsAtom;
		$atom->item_id = $this->id;
		$atom->app_root = Dase_Config::get('app_root');
		if (!$atom->findOne()) {
			$atom->insert();
		}

		$this_app_root_entry = null;
		//regenerate for EACH app root
		$atom = new Dase_DBO_ItemAsAtom;
		$atom->item_id = $this->id;
		foreach ($atom->find() as $found) {
			$c = $this->getCollection();
			$entry = new Dase_Atom_Entry_Item;
			$entry = $this->injectAtomEntryData($entry,$c,$found->app_root);
			$atom->item_type_ascii_id = $this->getItemType()->ascii_id;
			$atom->relative_url = 'item/'.$c->ascii_id.'/'.$this->serial_number;
			$atom->updated = date(DATE_ATOM);
			$atom->app_root = $found->app_root;
			$atom->xml = $entry->asXml($entry->root); //so we don't get xml declaration
			$atom->update();
			if (Dase_Config::get('app_root') == $found->app_root) {
				$this_app_root_entry = $entry;
			}
		}
		return $this_app_root_entry;
	}

	public static function getByUrl($url)
	{
		$app_root = Dase_Config::get('app_root');
		$path = str_replace($app_root,'',$url);
		if (strpos($path,'.') !== false) {
			$parts = explode('.', $path);
			$ext = array_pop($parts);
			if (isset(Dase_Http_Request::$types[$ext])) {
				$path = join('.',$parts);
			} else {	
				//path remains what it originally was
			}
		}
		$sections = explode('/',trim($path,'/'));
		$sernum = array_pop($sections);
		$coll = array_pop($sections);
		//will return false if no such item
		return Dase_DBO_Item::get($coll,$sernum);
	}

	public function deleteSearchIndexes()
	{
		$prefix = Dase_Config::get('table_prefix');
		$db = Dase_DB::get();
		$sql = "
			DELETE
			FROM {$prefix}search_table 
			WHERE item_id = $this->id
			";
		$db->query($sql);
		$sql = "
			DELETE
			FROM {$prefix}admin_search_table 
			WHERE item_id = $this->id
			";
		$db->query($sql);
	}

	public function buildSearchIndex()
	{
		//todo: should this be here??
		$this->saveAtom();

		$prefix = Dase_Config::get('table_prefix');
		$db = Dase_DB::get();
		//todo: make sure item->id is an integer
		$sql = "
			DELETE
			FROM {$prefix}search_table 
			WHERE item_id = $this->id
			";
		$db->query($sql);
		$sql = "
			DELETE
			FROM {$prefix}admin_search_table 
			WHERE item_id = $this->id
			";
		$db->query($sql);
		//search table
		$composite_value_text = '';
		$db = Dase_DB::get();
		$sql = "
			SELECT value_text
			FROM {$prefix}value v
			WHERE v.item_id = $this->id
			AND v.value_text != ''
			AND v.attribute_id in (SELECT id FROM {$prefix}attribute a where a.in_basic_search = true)
			";
		$st = $db->prepare($sql);
		$st->execute();
		//todo: this should be a foreach
		while ($value_text = $st->fetchColumn()) {
			$composite_value_text .= $value_text . " ";
		}

		//todo: fix this to get the latest version of content only
		$content = $this->getContents();
		if ($content && $content->text) {
			$composite_value_text .= $content->text . " ";
		}
		$c = $this->getCollection();
		$search_table = new Dase_DBO_SearchTable;
		$search_table->value_text = $composite_value_text;
		$search_table->item_id = $this->id;
		$search_table->collection_id = $this->collection_id;
		$search_table->collection_ascii_id = $c->ascii_id;
		$search_table->updated = date(DATE_ATOM);
		if ($composite_value_text) {
			$search_table->insert();
		}

		//admin search table
		$composite_value_text = '';
		$sql = "
			SELECT value_text
			FROM {$prefix}value
			WHERE item_id = $this->id
			";
		$st = $db->prepare($sql);
		$st->execute();
		while ($value_text = $st->fetchColumn()) {
			$composite_value_text .= $value_text . " ";
		}
		$content = $this->getContents();
		if ($content && $content->text) {
			$composite_value_text .= $content->text . " ";
		}
		$search_table = new Dase_DBO_AdminSearchTable;
		$search_table->value_text = $composite_value_text;
		$search_table->item_id = $this->id;
		$search_table->collection_id = $this->collection_id;
		$search_table->collection_ascii_id = $c->ascii_id;
		$search_table->updated = date(DATE_ATOM);
		$search_table->insert();
		$this->updated = date(DATE_ATOM);
		$this->update();
		Dase_Log::debug("built indexes for " . $this->serial_number);
	}

	public function getRawMetadata()
	{
		$c = $this->getCollection();
		$prefix = Dase_Config::get('table_prefix');
		$metadata = array();
		$bound_params = array();
		$sql = "
			SELECT a.ascii_id, a.attribute_name,
			v.value_text,a.collection_id, v.id, 
			a.is_on_list_display, a.is_public
			FROM {$prefix}attribute a, {$prefix}value v
			WHERE v.item_id = ?
			AND v.attribute_id = a.id
			ORDER BY a.sort_order,v.value_text
			";
		$st = Dase_DBO::query($sql,array($this->id));
		while ($row = $st->fetch()) {
			$metadata[] = $row;
		}
		return $metadata;
	}

	public function getMetadata($att_ascii_id = '')
	{
		$app_root = Dase_Config::get('app_root');
		$c = $this->getCollection();
		$prefix = Dase_Config::get('table_prefix');
		$metadata = array();
		$bound_params = array();
		$sql = "
			SELECT a.ascii_id, a.attribute_name,
			v.value_text,a.collection_id, v.id, 
			a.is_on_list_display, a.is_public
			FROM {$prefix}attribute a, {$prefix}value v
			WHERE v.item_id = ?
			AND v.attribute_id = a.id
			AND a.collection_id != 0
			";
		$bound_params[] = $this->id;
		if ($att_ascii_id) {
			$sql .= "
				AND a.ascii_id = ?
				";
			$bound_params[] = $att_ascii_id;
		}
		$sql .= "
			ORDER BY a.sort_order,v.value_text
			";
		$st = Dase_DBO::query($sql,$bound_params);
		while ($row = $st->fetch()) {
			$row['href'] = $app_root.'/attribute/'.$c->ascii_id.'/'.$row['ascii_id'];
			$metadata[] = $row;
		}
		return $metadata;
	}

	public function getMetadataAsCategories()
	{
		$cats = new Dase_Atom_Categories;
		$cats->setScheme('http://daseproject.org/category/metadata');
		foreach($this->getMetadata() as $m) {
			$cats->addCategory(
				$m['href'],'http://daseproject.org/category/metadata',
				$m['attribute_name'],$m['value_text']);
		}
		return $cats->asXml();
	}

	public function getAdminMetadata($att_ascii_id = '')
	{
		$app_root = Dase_Config::get('app_root');
		$prefix = Dase_Config::get('table_prefix');
		$metadata = array();
		$bound_params = array();
		$sql = "
			SELECT a.ascii_id, a.attribute_name,
			v.value_text,a.collection_id, v.id, 
			a.is_on_list_display, a.is_public
			FROM {$prefix}attribute a, {$prefix}value v
			WHERE v.item_id = ?
			AND v.attribute_id = a.id
			AND a.collection_id = 0
			";
		$bound_params[] = $this->id;
		if ($att_ascii_id) {
			$sql .= "
				AND a.ascii_id = ?
				";
			$bound_params[] = $att_ascii_id;
		}
		$sql .= "
			ORDER BY a.sort_order,v.value_text
			";
		$st = Dase_DBO::query($sql,$bound_params);
		while ($row = $st->fetch()) {
			$row['href'] = $app_root.'/attribute/'.$row['ascii_id'];
			$metadata[] = $row;
		}
	
		return $metadata;
	}

	//used for edit metadata form
	public function getMetadataJson()
	{
		$c = $this->getCollection();
		$app_root = Dase_Config::get('app_root');
		$prefix = Dase_Config::get('table_prefix');
		$metadata = array();
		$bound_params = array();
		$sql = "
			SELECT a.id as att_id,a.ascii_id,
			a.attribute_name,a.html_input_type,
			v.value_text,v.id as value_id, a.collection_id
			FROM {$prefix}attribute a, {$prefix}value v
			WHERE v.item_id = ?
			AND v.attribute_id = a.id
			ORDER BY a.sort_order,v.value_text
			";
		$bound_params[] = $this->id;
		$st = Dase_DBO::query($sql,$bound_params);
		while ($row = $st->fetch()) {
			$set = array();
			$set['value_id'] = $row['value_id'];
			$set['url'] = $app_root.'/item/'.$c->ascii_id.'/'.$this->serial_number.'/metadata/'.$row['value_id'];
			$set['collection_id'] = $row['collection_id'];
			$set['att_ascii_id'] = $row['ascii_id'];
			$set['attribute_name'] = $row['attribute_name'];
			$set['html_input_type'] = $row['html_input_type'];
			$set['value_text'] = $row['value_text'];
			if (in_array($row['html_input_type'],
				array('radio','checkbox','select','text_with_menu'))
			) {
				$att = new Dase_DBO_Attribute;
				$att->load($row['att_id']);
				$set['values'] = $att->getFormValues();
			}
			$metadata[] = $set;
		}
		return Dase_Json::get($metadata);
	}

	public function getValues()
	{
		$val = new Dase_DBO_Value;
		$val->item_id = $this->id;
		return $val->find();
	}

	public function getValue($att_ascii_id)
	{
		//only returns first found
		$prefix = Dase_Config::get('table_prefix');
		$sql = "
			SELECT v.value_text
			FROM {$prefix}attribute a, {$prefix}value v
			WHERE v.item_id = ?
			AND v.attribute_id = a.id
			AND a.ascii_id = ?
			LIMIT 1
			";
		$res = Dase_DBO::query($sql,array($this->id,$att_ascii_id),true)->fetch();
		if ($res && $res->value_text) {
			return $res->value_text;
		} else {
			return false;
		}
	}

	public function getCollection()
	{
		//avoids another db lookup
		if ($this->collection) {
			return $this->collection;
		}
		$c = new Dase_DBO_Collection;
		$c->load($this->collection_id);
		if ($c) {
			$this->collection = $c;
			return $c;
		} else {
			return false;
		}
	}

	public function getItemType()
	{
		if ($this->item_type) {
			return $this->item_type;
		}
		$item_type = new Dase_DBO_ItemType;
		if ($this->item_type_id) {
			$item_type->load($this->item_type_id);
		} else {
			$item_type->name = 'default';
			$item_type->ascii_id = 'default';
		}
		$this->item_type = $item_type;
		return $this->item_type;
	}

	public function getMedia($order_by='file_size')
	{
		Dase_Log::debug("getting media for " . $this->id);
		$c = $this->getCollection();
		$m = new Dase_DBO_MediaFile;
		$m->p_collection_ascii_id = $c->ascii_id;
		$m->p_serial_number = $this->serial_number;
		$m->orderBy($order_by);
		return $m->find();
	}

	public function getEnclosure()
	{
		$c = $this->getCollection();
		$m = new Dase_DBO_MediaFile;
		$m->p_collection_ascii_id = $c->ascii_id;
		$m->p_serial_number = $this->serial_number;
		$m->addWhere('file_size','null','is not');
		//todo: make sure file_size has values!
		$m->orderBy('file_size DESC');
		//$m->orderBy('width DESC');
		return $m->findOne();
	}

	public function getMediaRelativeUrl($size)
	{  //size really means type here
		$c = $this->getCollection();
		$m = new Dase_DBO_MediaFile;
		$m->p_collection_ascii_id = $c->ascii_id;
		$m->p_serial_number = $this->serial_number;
		$m->size = $size;
		if ($m->findOne()) {
			$url = "media/{$c->ascii_id}/$size/$m->filename";
			return $url;
		} else {
			return false;
		}
	}

	function getMediaCount()
	{
		$prefix = Dase_Config::get('table_prefix');
		$this->collection || $this->getCollection();
		$db = Dase_DB::get();
		$sql = "
			SELECT count(*) 
			FROM {$prefix}media_file
			WHERE p_serial_number = ?
			AND p_collection_ascii_id = ?
			";
		return Dase_DBO::query($sql,array($this->serial_number,$this->collection->ascii_id),true)->fetchColumn();
	}

	function setItemType($type_ascii_id='')
	{
		if (!$type_ascii_id || 'none' == $type_ascii_id) {
			$this->item_type_id = 0;
			$this->update();
			return true;
		}
		$type = new Dase_DBO_ItemType;
		$type->ascii_id = $type_ascii_id;
		$type->collection_id = $this->collection_id;
		if ($type->findOne()) {
			$this->item_type_id = $type->id;
			$this->update();
			return true;
		} else {
			return false;
		}
	}

	function getChildTypesList()
	{
		$children = array();
		$itr = new Dase_DBO_ItemTypeRelation;
		$itr->collection_ascii_id = $this->getCollection()->ascii_id;
		$itr->parent_type_ascii_id = $this->getItemType()->ascii_id;
		foreach ($itr->find() as $rel) {
			$children[$rel->child_type_ascii_id] = array(
				'title' => $rel->title,
				'count' => $rel->getChildCount($this->serial_number),
			);
		}
		return $children;
	}

	function updateMetadata($value_id,$value_text,$eid)
	{
		$c = $this->getCollection();
		$v = new Dase_DBO_Value;
		$v->load($value_id);
		$att = $v->getAttribute();
		$rev = new Dase_DBO_ValueRevisionHistory;
		$rev->added_text = $value_text;
		$rev->attribute_name = $att->attribute_name;
		$rev->collection_ascii_id = $c->ascii_id;
		$rev->dase_user_eid = $eid;
		$rev->deleted_text = $v->value_text;
		$rev->item_serial_number = $this->serial_number;
		$rev->timestamp = date(DATE_ATOM);
		$rev->insert();
		$v->value_text = $value_text;
		$v->update();
		$this->buildSearchIndex();
	}

	function removeMetadata($value_id,$eid)
	{
		$c = $this->getCollection();
		$v = new Dase_DBO_Value;
		$v->load($value_id);
		$att = $v->getAttribute();
		$rev = new Dase_DBO_ValueRevisionHistory;
		$rev->added_text = '';
		$rev->attribute_name = $att->attribute_name;
		$rev->collection_ascii_id = $c->ascii_id;
		$rev->dase_user_eid = $eid;
		$rev->deleted_text = $v->value_text;
		$rev->item_serial_number = $this->serial_number;
		$rev->timestamp = date(DATE_ATOM);
		$rev->insert();
		$v->delete();
		$this->buildSearchIndex();
	}

	function setValue($att_ascii_id,$value_text)
	{
		//todo: set value revision history as well
		$c = $this->getCollection();
		$att = new Dase_DBO_Attribute;
		$att->ascii_id = $att_ascii_id;
		//allows for admin metadata, att_ascii for which
		//always begins 'admin_'
		//NOTE: we now create att if it does not exist
		if (false === strpos($att_ascii_id,'admin_')) {
			$att = Dase_DBO_Attribute::findOrCreate($c->ascii_id,$att_ascii_id);
		} else {
			$att = Dase_DBO_Attribute::findOrCreateAdmin($att_ascii_id);
		}
		if ($att) {
			$v = new Dase_DBO_Value;
			$v->item_id = $this->id;
			$v->attribute_id = $att->id;
			$v->value_text = trim($value_text);
			return($v->insert());
		} else {
			//simply returns false if no such attribute
			Dase_Log::debug('[WARNING] no such attribute '.$att_ascii_id);
			return false;
		}
	}

	function deleteValues()
	{
		//should sanity check and archive values
		$admin_ids = Dase_DBO_Attribute::listAdminAttIds();
		$v = new Dase_DBO_Value;
		$v->item_id = $this->id;
		foreach ($v->find() as $doomed) {
			//do not delete admin att values
			if (!in_array($doomed->attribute_id,$admin_ids)) {
				$doomed->delete();
			}
		}
	}

	function deleteAdminValues()
	{
		$a = new Dase_DBO_Attribute;
		$a->collection_id = 0;
		foreach ($a->find() as $aa) {
			$v = new Dase_DBO_Value;
			$v->item_id = $this->id;
			$v->attribute_id = $aa->id;
			foreach ($v->find() as $doomed) {
				$doomed->delete();
			}
		}
		return "deleted admin metadata for " . $this->serial_number . "\n";
	}

	function expunge()
	{
		$c = $this->getCollection();
		$filename = Dase_Config::get('path_to_media').'/'.$c->ascii_id.'/deleted/'.$this->serial_number.'.atom';
		file_put_contents($filename,$this->asAtom());
		
		$this->deleteMedia();
		$this->deleteValues();
		$this->deleteAdminValues();
		$this->deleteSearchIndexes();
		$this->deleteContent();
		$this->deleteComments();
		$this->deleteTagItems();
		$this->deleteItemRelations();
		$this->deleteItemAsAtom();
		$this->delete();
		$c->updateItemCount();
	}

	function deleteItemAsAtom()
	{
		$atom = Dase_DBO_ItemAsAtom::getByItemId($this->id);
		if ($atom) {
			$atom->delete();
		}
	}

	function deleteItemRelations()
	{
		$irs = new Dase_DBO_ItemRelation;
		$irs->parent_serial_number = $this->serial_number;
		foreach ($irs->find() as $ir) {
			$child = $ir->getChild();
			$ir->delete();
			$child->saveAtom();
		}
		$irs = new Dase_DBO_ItemRelation;
		$irs->child_serial_number = $this->serial_number;
		foreach ($irs->find() as $ir) {
			//redo parent atom cache
			$parent = $ir->getParent();
			$ir->delete();
			$parent->saveAtom();
		}
	}

	function deleteContent()
	{
		$co = new Dase_DBO_Content;
		$co->item_id = $this->id;
		foreach ($co->find() as $doomed) {
			$doomed->delete();
		}
	}

	function deleteComments()
	{
		$co = new Dase_DBO_Comment;
		$co->item_id = $this->id;
		foreach ($co->find() as $doomed) {
			$doomed->delete();
		}
	}

	function deleteTagItems()
	{
		$tag_item = new Dase_DBO_TagItem;
		$tag_item->item_id = $this->id;
		$tags = array();
		foreach ($tag_item->find() as $doomed) {
			$tag = $doomed->getTag();
			$doomed->delete();
			$tag->updateItemCount();
		}
	}

	function deleteMedia()
	{
		$mf = new Dase_DBO_MediaFile;
		$mf->item_id = $this->id;
		foreach ($mf->find() as $doomed) {
			$doomed->delete();
		}
	}

	function getTitle()
	{
		$prefix = Dase_Config::get('table_prefix');
		$sql = "
			SELECT v.value_text 
			FROM {$prefix}attribute a, {$prefix}value v
			WHERE a.id = v.attribute_id
			AND a.ascii_id = 'title'
			AND v.item_id = ? 
			";
		$title = Dase_DBO::query($sql,array($this->id))->fetchColumn();
		if (!$title) {
			$title = $this->serial_number;
		}
		return $title;
	}

	function getDescription()
	{
		$prefix = Dase_Config::get('table_prefix');
		$sql = "
			SELECT v.value_text 
			FROM {$prefix}attribute a, {$prefix}value v
			WHERE a.id = v.attribute_id
			AND a.ascii_id = 'description'
			AND v.item_id = ? 
			";
		$description = Dase_DBO::query($sql,array($this->id))->fetchColumn();
		if (!$description) {
			$description = $this->getTitle();
		}
		return $description;
	}

	function getRights()
	{
		$prefix = Dase_Config::get('table_prefix');
		$sql = "
			SELECT v.value_text 
			FROM {$prefix}attribute a, {$prefix}value v
			WHERE a.id = v.attribute_id
			AND a.ascii_id = 'rights'
			AND v.item_id = ? 
			";
		$text = Dase_DBO::query($sql,array($this->id))->fetchColumn();
		if (!$text) { $text = 'daseproject.org'; }
		return $text;
	}

	public function getRelatedItems()
	{
		$related = array();
		$c = $this->getCollection();
		$item_relations = new Dase_DBO_ItemRelation;
		$item_relations->collection_ascii_id = $c->ascii_id;
		$item_relations->child_serial_number = $this->serial_number;
		foreach ($item_relations->find() as $item_relation) {
			$parent_item = Dase_DBO_Item::get($item_relation->collection_ascii_id,
				$item_relation->parent_serial_number);
			if ($parent_item) {
				$related[] = $parent_item;
			}
		}
		$item_relations = new Dase_DBO_ItemRelation;
		$item_relations->collection_ascii_id = $c->ascii_id;
		$item_relations->parent_serial_number = $this->serial_number;
		foreach ($item_relations->find() as $item_relation) {
			$child_item = Dase_DBO_Item::get($item_relation->collection_ascii_id,
				$item_relation->child_serial_number);
			if ($child_item) {
				$related[] = $child_item;
			}
		}
		return $related;
	}

	public function getParentItemsArray()
	{
		$parent_items = array();
		$c = $this->getCollection();
		$item_relations = new Dase_DBO_ItemRelation;
		$item_relations->collection_ascii_id = $c->ascii_id;
		$item_relations->child_serial_number = $this->serial_number;
		foreach ($item_relations->find() as $item_relation) {
			$parent_type = $item_relation->getParentType();
			$parent_item = Dase_DBO_Item::get($item_relation->collection_ascii_id,
				$item_relation->parent_serial_number);
			if ($parent_item) {
				$label = $parent_type->name.': '.$parent_item->getTitle();
				$url = $parent_item->getRelativeUrl($c->ascii_id);
				$parent_items[$url] = array(
					'label' => $label,
					'item_type' => $parent_type->ascii_id,
				);
			}
		}
		return $parent_items;
	}

	public function getParentTypes()
	{
		$types = array();
		foreach ($this->getItemType()->getParentRelations() as $rel) {
			$parent_type = $rel->getParentType();
			$types[] = $parent_type;
		}
		return $types;
	}

	function injectAtomEntryData(Dase_Atom_Entry $entry,$c = null,$app_root='')
	{
		if (!$this->id) { return false; }

		if (!$app_root) {
			$app_root = Dase_Config::get('app_root');
		}

		/* namespaces */

		$d = Dase_Atom::$ns['d'];
		$thr = Dase_Atom::$ns['thr'];

		/* resources */

		if ($c) {
			$this->collection = $c;
		} else {
			//lookup
			$c = $this->getCollection();
		}	

		$base_url = $app_root.'/item/'.$c->ascii_id.'/'.$this->serial_number;
		$type = $this->getItemType();

		/* standard atom stuff */

		$entry->setId($base_url);

		$entry->addAuthor($this->created_by_eid);
		//todo: I think this can be simplified when DASe 1.0 is retired
		if (is_numeric($this->updated)) {
			$entry->setUpdated(date(DATE_ATOM,$this->updated));
		} else {
			$entry->setUpdated($this->updated);
		}
		if (is_numeric($this->created)) {
			$entry->setPublished(date(DATE_ATOM,$this->created));
		} else {
			$entry->setPublished($this->created);
		}

		//atompub
		$entry->setEdited($entry->getUpdated());

		//alternate link
		$entry->addLink($app_root.'/item/'.$c->ascii_id.'/'.$this->serial_number,'alternate');

		//link to item metadata json, used for editing metadata
		$entry->addLink($app_root.'/item/'.$c->ascii_id.'/'.$this->serial_number.'/metadata.json','http://daseproject.org/relation/metadata','application/json');

		/* edit, dase/edit (json), service, and attributes (json) links
		 * for item.  If item has a type, that is the parent (atompub) collection
		 * otherwise, the collection is the parent (atompub) collection
		 */

		if ('default' == $type->ascii_id) {
			$entry->addLink(
				$app_root.'/item/'.$c->ascii_id.'/'.$this->serial_number.'.atom',
				'edit','application/atom+xml');
			$entry->addLink(
				$app_root.'/item/'.$c->ascii_id.'/'.$this->serial_number.'.json',
				'http://daseproject.org/relation/edit','application/json');
			$entry->addLink(
				$app_root.'/collection/'.$c->ascii_id.'/service',
				'service','application/atomsvc+xml','',$c->collection_name.' Item Type Service Doc' );
			$entry->addLink(
				$app_root.'/collection/'.$c->ascii_id.'/attributes.json',
				'http://daseproject.org/relation/attributes',
				'application/json','',$c->collection_name.' Attributes' );
		} else {
			$entry->addLink(
				$app_root.'/item_type/'.$c->ascii_id.'/'.$type->ascii_id.'/item/'.$this->serial_number.'.atom',
				'edit','application/atom+xml');
			$entry->addLink(
				$app_root.'/item_type/'.$c->ascii_id.'/'.$type->ascii_id.'/item/'.$this->serial_number.'.json',
				'http://daseproject.org/relation/edit','application/json');
			$entry->addLink(
				$app_root.'/item_type/'.$c->ascii_id.'/'.$type->ascii_id.'/service',
				'service','application/atomsvc+xml','',$type->name.' Item Type Service Doc' );
			$entry->addLink(
				$app_root.'/item_type/'.$c->ascii_id.'/'.$type->ascii_id.'/attributes.json',
				'http://daseproject.org/relation/attributes','application/json','',$type->name.' Attributes' );
		}

		/* parents link (can be posted to) */
		$entry->addLink($app_root.'/item/'.$c->ascii_id.'/'.$this->serial_number.'/parents','http://daseproject.org/relation/parents');

		/* threading extension */

		$replies = $entry->addLink($app_root.'/item/'.$c->ascii_id.'/'.$this->serial_number.'/comments','replies' );
		$thr_count = $this->getCommentsCount();
		if ($thr_count) {
			//lookup
			$replies->setAttributeNS($thr,'thr:count',$thr_count);
			//lookup
			$replies->setAttributeNS($thr,'thr:updated',$this->getCommentsUpdated());
		}

		/* dase categories */

		$entry->setEntrytype('item');

		//allows us to replace all if/when necessary :(
		$entry->addCategory($app_root,"http://daseproject.org/category/base_url");

		$entry->addCategory($this->item_type->ascii_id,
			'http://daseproject.org/category/item_type',$this->item_type->name);
		$entry->addCategory($c->ascii_id,
			'http://daseproject.org/category/collection',$c->collection_name);
		$entry->addCategory($this->id,'http://daseproject.org/category/item_id');
		$entry->addCategory($this->serial_number,'http://daseproject.org/category/serial_number');

		if ($this->status) {
			$entry->addCategory($this->status,'http://daseproject.org/category/status');
		} else {
			$entry->addCategory('public','http://daseproject.org/category/status');
		}

		/* categories for metadata */

		foreach ($this->getRawMetadata() as $row) {
			if (0 == $row['collection_id']) {
				$meta = $entry->addCategory(
					$row['ascii_id'],'http://daseproject.org/category/admin_metadata',
					$row['attribute_name'],$row['value_text']);
			} else {
				if ($row['is_public']) {
					$meta = $entry->addCategory(
						$row['ascii_id'],'http://daseproject.org/category/metadata',
						$row['attribute_name'],$row['value_text']);
					$meta->setAttributeNS($d,'d:edit-id',$app_root.'/item/'.$c->ascii_id.'/'.$this->serial_number.'/metadata/'.$row['id']);
				} else {
					$meta = $entry->addCategory(
						$row['ascii_id'],'http://daseproject.org/category/private_metadata',
						$row['attribute_name'],$row['value_text']);
					$meta->setAttributeNS($d,'d:edit-id',$app_root.'/item/'.$c->ascii_id.'/'.$this->serial_number.'/metadata/'.$row['id']);
				}
				if ('title' == $row['ascii_id'] || 'Title' == $row['attribute_name']) {
					$entry->setTitle($row['value_text']);
				}
				if ('rights' == $row['ascii_id']) {
					$entry->setRights($row['value_text']);
				}
			}
		}

		//this will only "take" if there is not already a title
		$entry->setTitle($this->serial_number);

		/**************************
		 *    hierarchy stuff
		 * ************************/

		if ('default' != $type->name) {

			//simply creates link that points to childern

			foreach ($this->getChildTypesList() as $child => $set) {
				//one for the Atom version
				$entry->addLink(
					$app_root.'/item_type/'.$c->ascii_id.'/'.$child.'/children_of/'.$type->ascii_id.'/'.$this->serial_number.'.atom',
					'http://daseproject.org/relation/childfeed','application/atom+xml','',$set['title'])
					->setAttributeNS(Dase_Atom::$ns['thr'],'thr:count',$set['count']);
				//one for the JSON version
				$entry->addLink(
					$app_root.'/item_type/'.$c->ascii_id.'/'.$child.'/children_of/'.$type->ascii_id.'/'.$this->serial_number.'.json',
					'http://daseproject.org/relation/childfeed','application/json','',$set['title'])
					->setAttributeNS(Dase_Atom::$ns['thr'],'thr:count',$set['count']);
			}


			//adds a link to any parent item(s)
			foreach ($this->getParentItemsArray() as $url => $set) {
				$entry->addLink($app_root.'/'.$url,'http://daseproject.org/relation/parent','','',$set['label'])
					->setAttributeNS(Dase_Atom::$ns['d'],'d:item_type',$set['item_type']);
			}

			/* creates a link to the parent types items (in json)
			 * so you indicate linking is available AND
			 * suitable for creating a pull-down menu
			 */
			foreach ($this->getParentTypes() as $pt) {
				$entry->addLink($app_root.'/'.$pt->getRelativeUrl($c->ascii_id).'.json',
					'http://daseproject.org/relation/parent_item_type','application/json','',$pt->name);
			}
		}

		/* content */

		$content = $this->getContents();
		if ($content && $content->text) {
			if ('application/json' == $content->type) {
				$entry->setExternalContent($base_url.'/content','application/json');
			} else {
				$entry->setContent($content->text,$content->type);
			}
		} 

		/* put thumbnail in summary */
		$thumb_url = $this->getMediaRelativeUrl('thumbnail');
		if ($thumb_url) {
			$entry->setThumbnail($app_root.'/'.$thumb_url);	
		}

		/* enclosure */

		$enc = $this->getEnclosure();
		if ($enc) {
			$entry->addLink($app_root.'/'.$this->getMediaRelativeUrl($enc->size),'enclosure',$enc->mime_type,$enc->file_size);
		}

		/* edit-media link */

		$entry->addLink($app_root.'/'.$this->getEditMediaRelativeUrl($c->ascii_id),'edit-media');

		/* media rss ext */

		foreach ($this->getMedia() as $med) {
			if ('thumbnail' == $med->size) {
				$media_thumbnail = $entry->addElement('media:thumbnail','',Dase_Atom::$ns['media']);
				$media_thumbnail->setAttribute('url',$med->getLink());
				$media_thumbnail->setAttribute('width',$med->width);
				$media_thumbnail->setAttribute('height',$med->height);
			} else {
				$media_content = $entry->addElement('media:content','',Dase_Atom::$ns['media']);
				$media_content->setAttribute('url',$med->getLink());
				if ($med->width && $med->height) {
					$media_content->setAttribute('width',$med->width);
					$media_content->setAttribute('height',$med->height);
				}
				$media_content->setAttribute('fileSize',$med->file_size);
				$media_content->setAttribute('type',$med->mime_type);
				$media_category = $media_content->appendChild($entry->dom->createElement('media:category'));
				$media_category->appendChild($entry->dom->createTextNode($med->size));
			}
		}
		return $entry;
	}

	function injectAtomFeedData(Dase_Atom_Feed $feed)
	{
		if (!$this->id) { return false; }
		$app_root = Dase_Config::get('app_root');
		$c = $this->getCollection();
		if (is_numeric($this->updated)) {
			$updated = date(DATE_ATOM,$this->updated);
		} else {
			$updated = $this->updated;
		}
		$feed->setUpdated($updated);
		$feed->setTitle($this->getTitle());
		$feed->setId('tag:'.Dase_Util::getUniqueName());
		$feed->addLink($app_root.'/item/'.$c->ascii_id.'/'.$this->serial_number.'.atom','self' );
		$feed->addAuthor();
		return $feed;
	}

	function asAtom()
	{
		$feed = new Dase_Atom_Feed;
		$this->injectAtomFeedData($feed);
		$feed->setFeedType('item');
		//todo: this needs to be passed in?
		$feed->addCategory('browse',"http://daseproject.org/category/tag_type",'browse');
		$feed->addItemEntry($this); //checks cache 
		//add comments
		foreach ($this->getComments() as $comment) {
			$entry = $feed->addEntry('comment');
			$comment->injectAtomEntryData($entry);
		}
		//todo: this may be TOO expensive
		foreach ($this->getRelatedItems() as $related) {
			$feed->addItemEntry($related);
		}
		return $feed->asXml();
	}

	function asAtomEntry()
	{
		$atom = Dase_DBO_ItemAsAtom::getByItemId($this->id);
		if ($atom) {
			$entry = Dase_Atom_Entry_Item::load($atom->xml);
		} else {
			$entry = $this->saveAtom();
		}
		return $entry->asXml();
	}

	/** experimental */
	function asAtomJson()
	{
		$entry = new Dase_Atom_Entry;
		$this->injectAtomEntryData($entry);
		return $entry->asJson();
	}

	function mediaAsAtomFeed() 
	{
		$feed = new Dase_Atom_Feed;
		$this->injectAtomFeedData($feed);
		foreach ($this->getMedia('updated DESC') as $m) {
			$entry = $feed->addEntry();
			$m->injectAtomEntryData($entry);
		}
		return $feed->asXml();
	}	

	public function getRelativeUrl($coll='') 
	{
		if (!$coll) {
			$coll = $this->getCollection()->ascii_id;
		}
		return 'item/'.$coll.'/'.$this->serial_number;
	}

	public function getEditMediaRelativeUrl($coll='')
	{
		if (!$coll) {
			$coll = $this->getCollection()->ascii_id;
		}
		return 'media/'.$coll.'/'.$this->serial_number;
	}

	public function getAtomPubServiceDoc() {
		$app_root = Dase_Config::get('app_root');
		$c = $this->getCollection();
		$app = new Dase_Atom_Service;
		$workspace = $app->addWorkspace($c->collection_name.' Item '.$this->serial_number.' Workspace');
		$media_coll = $workspace->addCollection($app_root.'/item/'.$c->ascii_id.'/'.$this->serial_number.'/media.atom',$c->collection_name.' Item '.$this->serial_number.' Media'); 
		foreach(Dase_Config::get('media_types') as $type) {
			$media_coll->addAccept($type);
		}
		$parents_coll = $workspace->addCollection($app_root.'/item/'.$c->ascii_id.'/'.$this->serial_number.'/parents.atom',$c->collection_name.' Item '.$this->serial_number.' Parents'); 
		$parents_coll->addAccept('text/uri-list');
		$comments_coll = $workspace->addCollection($app_root.'/item/'.$c->ascii_id.'/'.$this->serial_number.'/comments.atom',$c->collection_name.' Item '.$this->serial_number.' Comments'); 
		$comments_coll->addAccept('text/plain');
		$comments_coll->addAccept('text/html');
		$comments_coll->addAccept('application/xhtml+xml');
		return $app->asXml();
	}

	public function asArray()
	{
		$j = array();
		$app_root = Dase_Config::get('app_root');
		$c = $this->getCollection();
		$item_array['serial_number'] = $this->serial_number;
		$item_array['created'] = $this->created;
		$item_array['updated'] = $this->updated;
		$item_array['collection'] = $c->ascii_id;
		$item_array['edit'] = $app_root.'/item/'.$c->ascii_id.'/'.$this->serial_number.'.atom';
		$item_array['metadata'] = array();
		foreach ($this->getMetadata() as $row) {
			//note: a simpler way would be to ALWAYS make value an array.
			//but this is a bit more concise (only an array if multiple) 
			if (isset($item_array['metadata'][$row['ascii_id']])) {
				if (is_array($item_array['metadata'][$row['ascii_id']])) {
					$item_array['metadata'][$row['ascii_id']][] = $row['value_text'];
				} else {
					$orig = $item_array['metadata'][$row['ascii_id']];
					$item_array['metadata'][$row['ascii_id']] = array();;
					$item_array['metadata'][$row['ascii_id']][] = $orig;
					$item_array['metadata'][$row['ascii_id']][] = $row['value_text'];
				}
			} else {
				$item_array['metadata'][$row['ascii_id']] = $row['value_text'];
			}
		}
		$item_array['media'] = array();
		foreach ($this->getMedia() as $m) {
		$item_array['media'][$m->size] = 
			$app_root.'/media/'.
			$c->ascii_id.'/'.$m->size.'/'.$m->filename;
		}
		$con = $this->getContents();
		if ($con) {
			$content[$con->id]['updated'] = $con->updated;
			$content[$con->id]['eid'] = $con->updated_by_eid;
			$content[$con->id]['text'] = $con->text;
			$item_array['content'][] = $content;
		}

		return $item_array;
	}

	public function asJson()
	{
		return Dase_Json::get($this->asArray(),true);
	}

	public function statusAsJson()
	{
		$labels['public'] = "Public";
		$labels['draft'] = "Draft (Admin View Only)";
		$labels['delete'] = "Marked for Deletion";
		$labels['archive'] = "In Deep Storage";

		$status['term'] = $this->status;
		$status['label'] = $labels[$this->status];

		return Dase_Json::get($status);
	}

	/** pass in true to get all versions */
	public function getContents($get_all=false)
	{
		$contents = new Dase_DBO_Content;
		$contents->item_id = $this->id;
		$contents->orderBy('updated DESC');
		if ($get_all) {
			return $contents->find();
		} else {
			return $contents->findOne();
		}
	}

	public function getCommentsCount()
	{
		$comments = new Dase_DBO_Comment;
		$comments->item_id = $this->id;
		return $comments->findCount();
	}

	public function getCommentsUpdated()
	{
		$comments = new Dase_DBO_Comment;
		$comments->item_id = $this->id;
		$comments->orderBy('updated DESC');
		$latest = $comments->findOne();
		return $latest->updated;
	}

	public function getComments($eid='')
	{
		$comments = new Dase_DBO_Comment;
		$comments->item_id = $this->id;
		if ($eid) {
			$comments->updated_by_eid = $eid;
		}
		return $comments->find();
	}

	public function getCommentsJson($eid='')
	{
		$comments = '';
		foreach ($this->getComments($eid) as $c_obj) {
			$c['id'] = $c_obj->id;
			$c['updated'] = $c_obj->updated;
			$c['updated'] = date('D M j, Y \a\t g:ia',strtotime($c_obj->updated));
			$c['eid'] = $c_obj->updated_by_eid;
			$c['text'] = $c_obj->text;
			$comments[] = $c;
		}
		return Dase_Json::get($comments);
	}

	public function getContentJson()
	{
		$c_obj = $this->getContents();
		$content = array();
		if ($c_obj) {
			$content['latest']['text'] = $c_obj->text;
			$content['latest']['date'] = $c_obj->updated;
		} else {
			$content['latest']['text'] = '';
			$content['latest']['date'] = ''; 
		}
		return Dase_Json::get($content);
	}

	public function setContent($text,$eid,$type="text")
	{
		$c = $this->getCollection();
		$content = new Dase_DBO_Content;
		$content->item_id = $this->id;
		//todo: security! filter input....
		$content->text = $text;
		$content->type = $type;
		$content->p_collection_ascii_id = $c->ascii_id;
		$content->p_serial_number = $this->serial_number;
		$content->updated = date(DATE_ATOM);
		$content->updated_by_eid = $eid;
		$res = $content->insert();
		$this->saveAtom();
		return $res;
	}

	public function addComment($text,$eid)
	{
		$c = $this->getCollection();
		$note = new Dase_DBO_Comment;
		$note->item_id = $this->id;
		//todo: security! filter input....
		$note->text = $text;
		$note->p_collection_ascii_id = $c->ascii_id;
		$note->p_serial_number = $this->serial_number;
		$note->updated = date(DATE_ATOM);
		$note->updated_by_eid = $eid;
		$res = $note->insert();
		$this->saveAtom();
		return $res;
	}

	public function getTags()
	{
		$tags = array();
		$tag_item = new Dase_DBO_TagItem;
		$tag_item->item_id = $this->id;
		foreach ($tag_item->find() as $ti) {
			$tags[] = $ti->getTag();
		}
		if (count($tags)) {
			return $tags;
		} else {
			return false;
		}
	}

	public static function sortIdArrayByUpdated($item_ids)
	{
		$prefix = Dase_Config::get('table_prefix');
		$db = Dase_DB::get();
		$sql = "
			SELECT updated 
			FROM {$prefix}item i
			WHERE i.id = ? 
			";
		$sth = $db->prepare($sql);
		foreach ($item_ids as $item_id) {
			$sth->execute(array($item_id));
			$updated = $sth->fetchColumn();
			$sortable_array[$item_id] = $updated;
		}
		if (is_array($sortable_array)) {
			arsort($sortable_array);
			return array_keys($sortable_array);
		}
	}

	public static function sortIdArray($sort,$item_ids)
	{
		$test_att = new Dase_DBO_Attribute;
		$test_att->ascii_id = $sort;
		if (!$test_att->findOne()) {
			return $item_ids;
		}
		$prefix = Dase_Config::get('table_prefix');
		$db = Dase_DB::get();
		$sql = "
			SELECT v.value_text
			FROM {$prefix}attribute a, {$prefix}value v
			WHERE v.item_id = ?
			AND v.attribute_id = a.id
			AND a.ascii_id = ?
			LIMIT 1
			";
		$sth = $db->prepare($sql);
		foreach ($item_ids as $item_id) {
			$sth->execute(array($item_id,$sort));
			$vt = $sth->fetchColumn();
			$value_text = $vt ? $vt : 99999999;
			$sortable_array[$item_id] = $value_text;
		}
		if (is_array($sortable_array)) {
			asort($sortable_array);
			return array_keys($sortable_array);
		}
	}

	/** expires any cache that might hold stale metadata */
	public function expireCaches()
	{
		// attributes json (includes tallies)
		$c = $this->getCollection();
		$cache_id = "get|collection/".$c->ascii_id."/attributes/public/tallies|json|cache_buster=stripped&format=json";
		Dase_Cache::get($cache_id)->expire();
	
	}
}
