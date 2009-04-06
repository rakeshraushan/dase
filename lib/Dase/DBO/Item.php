<?php

require_once 'Dase/DBO/Autogen/Item.php';

class Dase_DBO_Item extends Dase_DBO_Autogen_Item 
{

	public $collection = null;
	public $item_type;
	public $media = array();
	public $values = array();

	public static function get($db,$collection_ascii_id,$serial_number)
	{
		if (!$collection_ascii_id || !$serial_number) {
			throw new Exception('missing information');
		}
		$c = Dase_DBO_Collection::get($db,$collection_ascii_id);
		if (!$c) {
			return false;
		}
		$item = new Dase_DBO_Item($db);
		$item->collection_id = $c->id;
		$item->serial_number = $serial_number;
		return $item->findOne();
	}

	public function flushAtom()
	{
		$prefix = $this->db->table_prefix;
		$dbh = $this->db->getDbh();
		//todo: make sure item->id is an integer
		$sql = "
			DELETE
			FROM {$prefix}item_as_atom 
			WHERE item_id = $this->id
			";
		$dbh->query($sql);
	}

	public function saveAtom()
	{
		$app_root = '{APP_ROOT}';
		$db = $this->db;
		$atom = new Dase_DBO_ItemAsAtom($db);
		$atom->item_id = $this->id;
		$atom->app_root = $app_root;
		if ($atom->findOne()) {
			$entry = new Dase_Atom_Entry_Item;
			$entry = $this->injectAtomEntryData($entry,$app_root);
			$atom->item_type_ascii_id = $this->getItemType()->ascii_id;
			$atom->relative_url = 'item/'.$this->p_collection_ascii_id.'/'.$this->serial_number;
			$atom->updated = date(DATE_ATOM);
			$atom->xml = $entry->asXml($entry->root); //so we don't get xml declaration
			$atom->update();
		} else {
			$c = $this->getCollection();
			$entry = new Dase_Atom_Entry_Item;
			$entry = $this->injectAtomEntryData($entry,$app_root);
			$atom->item_type_ascii_id = $this->getItemType()->ascii_id;
			$atom->relative_url = 'item/'.$this->p_collection_ascii_id.'/'.$this->serial_number;
			$atom->updated = date(DATE_ATOM);
			$atom->xml = $entry->asXml($entry->root); //so we don't get xml declaration
			$atom->insert();
		}
		return $atom;
	}

	public static function getByUrl($db,$url)
	{
		//ignores everything but last two sections
		$url = str_replace('.atom','',$url);
		$sections = explode('/',trim($url,'/'));
		$sernum = array_pop($sections);
		$coll = array_pop($sections);
		//will return false if no such item
		return Dase_DBO_Item::get($db,$coll,$sernum);
	}

	public function deleteSearchIndexes()
	{
		$prefix = $this->db->table_prefix;
		$dbh = $this->db->getDbh();
		$sql = "
			DELETE
			FROM {$prefix}search_table 
			WHERE item_id = $this->id
			";
		$dbh->query($sql);
		$sql = "
			DELETE
			FROM {$prefix}admin_search_table 
			WHERE item_id = $this->id
			";
		$dbh->query($sql);
	}

	public function buildSearchIndex()
	{
		$db = $this->db;
		//todo: should this be here??
		$this->saveAtom();

		$prefix = $db->table_prefix;
		$dbh = $db->getDbh();
		//todo: make sure item->id is an integer
		$sql = "
			DELETE
			FROM {$prefix}search_table 
			WHERE item_id = $this->id
			";
		$dbh->query($sql);
		$sql = "
			DELETE
			FROM {$prefix}admin_search_table 
			WHERE item_id = $this->id
			";
		$dbh->query($sql);
		//search table
		$composite_value_text = '';
		$sql = "
			SELECT value_text
			FROM {$prefix}value v
			WHERE v.item_id = $this->id
			AND v.value_text != ''
			AND v.attribute_id in (SELECT id FROM {$prefix}attribute a where a.in_basic_search = true)
			";
		$st = $dbh->prepare($sql);
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
		$search_table = new Dase_DBO_SearchTable($db);
		$search_table->value_text = $composite_value_text;
		$search_table->item_id = $this->id;
		$search_table->collection_id = $this->collection_id;
		$search_table->collection_ascii_id = $this->p_collection_ascii_id;
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
		$st = $dbh->prepare($sql);
		$st->execute();
		while ($value_text = $st->fetchColumn()) {
			$composite_value_text .= $value_text . " ";
		}
		$content = $this->getContents();
		if ($content && $content->text) {
			$composite_value_text .= $content->text . " ";
		}
		$search_table = new Dase_DBO_AdminSearchTable($db);
		$search_table->value_text = $composite_value_text;
		$search_table->item_id = $this->id;
		$search_table->collection_id = $this->collection_id;
		$search_table->collection_ascii_id = $this->p_collection_ascii_id;
		$search_table->updated = date(DATE_ATOM);
		$search_table->insert();
		$this->updated = date(DATE_ATOM);
		$this->update();
		$this->log->debug("built indexes for " . $this->serial_number);
	}

	public function getRawMetadata()
	{
		$db = $this->db;
		$c = $this->getCollection();
		$prefix = $this->db->table_prefix;
		$metadata = array();
		$bound_params = array();
		$sql = "
			SELECT a.ascii_id, a.attribute_name,
			v.value_text,a.collection_id, v.id, 
			a.is_on_list_display, a.is_public,v.url
			FROM {$prefix}attribute a, {$prefix}value v
			WHERE v.item_id = ?
			AND v.attribute_id = a.id
			ORDER BY a.sort_order,v.value_text
			";
		$st = Dase_DBO::query($db,$sql,array($this->id));
		while ($row = $st->fetch()) {
			$metadata[] = $row;
		}
		return $metadata;
	}

	public function getMetadata($app_root='{APP_ROOT}',$att_ascii_id='')
	{
		$db = $this->db;
		$prefix = $this->db->table_prefix;
		$metadata = array();
		$bound_params = array();
		$sql = "
			SELECT a.ascii_id, a.attribute_name,
			v.value_text,a.collection_id, v.id, 
			a.is_on_list_display, a.is_public,v.url
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
		$st = Dase_DBO::query($db,$sql,$bound_params);
		while ($row = $st->fetch()) {
			$row['href'] = $app_root.'/attribute/'.$this->p_collection_ascii_id.'/'.$row['ascii_id'];
			$metadata[] = $row;
		}
		return $metadata;
	}

	public function getAdminMetadata($att_ascii_id = '',$app_root)
	{
		$db = $this->db;
		$prefix = $this->db->table_prefix;
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
		$st = Dase_DBO::query($db,$sql,$bound_params);
		while ($row = $st->fetch()) {
			$row['href'] = $app_root.'/attribute/'.$row['ascii_id'];
			$metadata[] = $row;
		}
	
		return $metadata;
	}

	//used for edit metadata form
	public function getMetadataJson($app_root)
	{
		$db = $this->db;
		$prefix = $this->db->table_prefix;
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
		$st = Dase_DBO::query($db,$sql,$bound_params);
		while ($row = $st->fetch()) {
			$set = array();
			$set['value_id'] = $row['value_id'];
			$set['url'] = $app_root.'/item/'.$this->p_collection_ascii_id.'/'.$this->serial_number.'/metadata/'.$row['value_id'];
			$set['collection_id'] = $row['collection_id'];
			$set['att_ascii_id'] = $row['ascii_id'];
			$set['attribute_name'] = $row['attribute_name'];
			$set['html_input_type'] = $row['html_input_type'];
			$set['value_text'] = $row['value_text'];
			if (in_array($row['html_input_type'],
				array('radio','checkbox','select','text_with_menu'))
			) {
				$att = new Dase_DBO_Attribute($this->db);
				$att->load($row['att_id']);
				$set['values'] = $att->getFormValues();
			}
			$metadata[] = $set;
		}
		return Dase_Json::get($metadata);
	}

	public function getValues()
	{
		$val = new Dase_DBO_Value($this->db);
		$val->item_id = $this->id;
		return $val->find();
	}

	public function getValue($att_ascii_id)
	{
		$db = $this->db;
		//only returns first found
		$prefix = $this->db->table_prefix;
		$sql = "
			SELECT v.value_text
			FROM {$prefix}attribute a, {$prefix}value v
			WHERE v.item_id = ?
			AND v.attribute_id = a.id
			AND a.ascii_id = ?
			LIMIT 1
			";
		$res = Dase_DBO::query($db,$sql,array($this->id,$att_ascii_id),true)->fetch();
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
		$db = $this->db;
		$c = new Dase_DBO_Collection($db);
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
		$db = $this->db;
		$item_type = new Dase_DBO_ItemType($db);
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
		$db = $this->db;
		$this->log->debug("getting media for " . $this->id);
		$m = new Dase_DBO_MediaFile($db);
		$m->p_collection_ascii_id = $this->p_collection_ascii_id;
		$m->p_serial_number = $this->serial_number;
		$m->orderBy($order_by);
		return $m->find();
	}

	public function getEnclosure()
	{
		$db = $this->db;
		$m = new Dase_DBO_MediaFile($db);
		$m->p_collection_ascii_id = $this->p_collection_ascii_id;
		$m->p_serial_number = $this->serial_number;
		$m->addWhere('file_size','null','is not');
		//todo: make sure file_size has values!
		$m->orderBy('file_size DESC');
		//$m->orderBy('width DESC');
		return $m->findOne();
	}

	public function getMediaUrl($size,$app_root='')
	{  //size really means type here
		$db = $this->db;
		$m = new Dase_DBO_MediaFile($db);
		$m->p_collection_ascii_id = $this->p_collection_ascii_id;
		$m->p_serial_number = $this->serial_number;
		$m->size = $size;
		if ($m->findOne()) {
			if ($app_root) {
				$url = $app_root."/media/{$this->p_collection_ascii_id}/$size/$m->filename";
			} else {
				$url = $app_root."/media/{$this->p_collection_ascii_id}/$size/$m->filename";
			}
			return $url;
		} else {
			return false;
		}
	}

	function getMediaCount()
	{
		$prefix = $this->db->table_prefix;
		$c = $this->getCollection();
		$sql = "
			SELECT count(*) 
			FROM {$prefix}media_file
			WHERE p_serial_number = ?
			AND p_collection_ascii_id = ?
			";
		return Dase_DBO::query($this->db,$sql,array($this->serial_number,$c->ascii_id),true)->fetchColumn();
	}

	function setItemType($type_ascii_id='')
	{
		if (!$type_ascii_id || 'none' == $type_ascii_id) {
			$this->item_type_id = 0;
			$this->update();
			return true;
		}
		$type = new Dase_DBO_ItemType($this->db);
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

	function updateMetadata($value_id,$value_text,$eid)
	{
		$v = new Dase_DBO_Value($this->db);
		$v->load($value_id);
		$att = $v->getAttribute();
		$rev = new Dase_DBO_ValueRevisionHistory($this->db);
		$rev->added_text = $value_text;
		$rev->attribute_name = $att->attribute_name;
		$rev->collection_ascii_id = $this->p_collection_ascii_id;
		$rev->dase_user_eid = $eid;
		$rev->deleted_text = $v->value_text;
		$rev->item_serial_number = $this->serial_number;
		$rev->timestamp = date(DATE_ATOM);
		$rev->insert();
		$v->value_text = $value_text;
		$v->update();
		//todo: queue up to do in another process
		//$this->buildSearchIndex();

		//experiment:
		//$this->flushAtom();

		$this->saveAtom();
	}

	function removeMetadata($value_id,$eid)
	{
		$v = new Dase_DBO_Value($this->db);
		$v->load($value_id);
		$att = $v->getAttribute();
		$rev = new Dase_DBO_ValueRevisionHistory($this->db);
		$rev->added_text = '';
		$rev->attribute_name = $att->attribute_name;
		$rev->collection_ascii_id = $this->p_collection_ascii_id;
		$rev->dase_user_eid = $eid;
		$rev->deleted_text = $v->value_text;
		$rev->item_serial_number = $this->serial_number;
		$rev->timestamp = date(DATE_ATOM);
		$rev->insert();
		$v->delete();
		$this->buildSearchIndex();
	}

	/** simple convenience method */
	function updateTitle($value_text,$eid)
	{
		//todo: set value revision history as well (using eid)
		$att = Dase_DBO_Attribute::findOrCreate($this->db,$this->p_collection_ascii_id,'title');
		if ($att) {
			$v = new Dase_DBO_Value($this->db);
			$v->item_id = $this->id;
			$v->attribute_id = $att->id;
			if ($v->findOne()) {
				$v->value_text = trim($value_text);
				$v->update();
			} else {
				$v->value_text = trim($value_text);
				$v->insert();
			}
			$this->saveAtom();
		}
	}

	function setValue($att_ascii_id,$value_text)
	{
		//todo: this needs work -- no need to 'new' an att
		//todo: set value revision history as well
		$att = new Dase_DBO_Attribute($this->db);
		$att->ascii_id = $att_ascii_id;
		//allows for admin metadata, att_ascii for which
		//always begins 'admin_'
		//NOTE: we now create att if it does not exist
		if (false === strpos($att_ascii_id,'admin_')) {
			$att = Dase_DBO_Attribute::findOrCreate($this->db,$this->p_collection_ascii_id,$att_ascii_id);
		} else {
			$att = Dase_DBO_Attribute::findOrCreateAdmin($this->db,$att_ascii_id);
		}
		if ($att) {
			$v = new Dase_DBO_Value($this->db);
			$v->item_id = $this->id;
			$v->attribute_id = $att->id;
			$v->value_text = trim($value_text);
			$v->insert();
			return $v;
			//too expensive:
			//$this->saveAtom();
		} else {
			//simply returns false if no such attribute
			$this->log->debug('[WARNING] no such attribute '.$att_ascii_id);
			return false;
		}
	}

	function deleteValues()
	{
		//should sanity check and archive values
		$admin_ids = Dase_DBO_Attribute::listAdminAttIds($this->db);
		$v = new Dase_DBO_Value($this->db);
		$v->item_id = $this->id;
		foreach ($v->find() as $doomed) {
			//do not delete admin att values
			if (!in_array($doomed->attribute_id,$admin_ids)) {
				$doomed->delete();
			}
		}
		$this->saveAtom();
	}

	function deleteAdminValues()
	{
		$a = new Dase_DBO_Attribute($this->db);
		$a->collection_id = 0;
		foreach ($a->find() as $aa) {
			$v = new Dase_DBO_Value($this->db);
			$v->item_id = $this->id;
			$v->attribute_id = $aa->id;
			foreach ($v->find() as $doomed) {
				$doomed->delete();
			}
		}
		return "deleted admin metadata for " . $this->serial_number . "\n";
	}

	function expunge($path_to_media='')
	{
		if ($path_to_media) {
			$filename = $path_to_media.'/'.$this->p_collection_ascii_id.'/deleted/'.$this->serial_number.'.atom';
			file_put_contents($filename,$this->asAtom('http://daseproject.org/deleted/'));
		}
		
		$this->deleteMedia($path_to_media);
		$this->deleteValues();
		$this->deleteAdminValues();
		$this->deleteSearchIndexes();
		$this->deleteContent();
		$this->deleteComments();
		$this->deleteTagItems();
		$this->deleteItemAsAtom();
		$this->delete();
		$this->getCollection()->updateItemCount();
	}

	function deleteItemAsAtom()
	{
		$atom = Dase_DBO_ItemAsAtom::getByItem($this);
		if ($atom) {
			$atom->delete();
		}
	}

	function deleteContent()
	{
		$co = new Dase_DBO_Content($this->db);
		$co->item_id = $this->id;
		foreach ($co->find() as $doomed) {
			$doomed->delete();
		}
	}

	function deleteComments()
	{
		$co = new Dase_DBO_Comment($this->db);
		$co->item_id = $this->id;
		foreach ($co->find() as $doomed) {
			$doomed->delete();
		}
	}

	function deleteTagItems()
	{
		$tag_item = new Dase_DBO_TagItem($this->db);
		$tag_item->item_id = $this->id;
		$tags = array();
		foreach ($tag_item->find() as $doomed) {
			$tag = $doomed->getTag();
			$doomed->delete();
			$tag->updateItemCount();
		}
	}

	function deleteMedia($path_to_media='')
	{
		$mf = new Dase_DBO_MediaFile($this->db);
		$mf->item_id = $this->id;
		foreach ($mf->find() as $doomed) {
			if ($path_to_media) {
				$doomed->moveFileToDeleted($path_to_media);
			}
			$doomed->delete();
		}
	}

	function getTitle()
	{
		$db = $this->db;
		$prefix = $this->db->table_prefix;
		$sql = "
			SELECT v.value_text 
			FROM {$prefix}attribute a, {$prefix}value v
			WHERE a.id = v.attribute_id
			AND a.ascii_id = 'title'
			AND v.item_id = ? 
			";
		$title = Dase_DBO::query($db,$sql,array($this->id))->fetchColumn();
		if (!$title) {
			$title = $this->serial_number;
		}
		return $title;
	}

	function getDescription()
	{
		$db = $this->db;
		$prefix = $this->db->table_prefix;
		$sql = "
			SELECT v.value_text 
			FROM {$prefix}attribute a, {$prefix}value v
			WHERE a.id = v.attribute_id
			AND a.ascii_id = 'description'
			AND v.item_id = ? 
			";
		$description = Dase_DBO::query($db,$sql,array($this->id))->fetchColumn();
		if (!$description) {
			$description = $this->getTitle();
		}
		return $description;
	}

	function getRights()
	{
		$db = $this->db;
		$prefix = $db->table_prefix;
		$sql = "
			SELECT v.value_text 
			FROM {$prefix}attribute a, {$prefix}value v
			WHERE a.id = v.attribute_id
			AND a.ascii_id = 'rights'
			AND v.item_id = ? 
			";
		$text = Dase_DBO::query($db,$sql,array($this->id))->fetchColumn();
		if (!$text) { $text = 'daseproject.org'; }
		return $text;
	}

	function injectAtomEntryData(Dase_Atom_Entry $entry,$app_root)
	{
		if (!$this->id) { return false; }

		/* namespaces */

		$d = Dase_Atom::$ns['d'];
		$thr = Dase_Atom::$ns['thr'];

		/* resources */

		$base_url = $app_root.'/item/'.$this->p_collection_ascii_id.'/'.$this->serial_number;
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
		$entry->addLink($app_root.'/item/'.$this->p_collection_ascii_id.'/'.$this->serial_number,'alternate');

		//link to item metadata json, used for editing metadata
		$entry->addLink($app_root.'/item/'.$this->p_collection_ascii_id.'/'.$this->serial_number.'/metadata.json','http://daseproject.org/relation/metadata','application/json');

		/* edit, dase/edit (json), service, and attributes (json) links
		 * for item.  If item has a type, that is the parent (atompub) collection
		 * otherwise, the collection is the parent (atompub) collection
		 */

		if ('default' == $type->ascii_id) {
			$entry->addLink(
				$app_root.'/item/'.$this->p_collection_ascii_id.'/'.$this->serial_number.'.atom',
				'edit','application/atom+xml');
			$entry->addLink(
				$app_root.'/item/'.$this->p_collection_ascii_id.'/'.$this->serial_number.'/content',
				'http://daseproject.org/relation/edit-content');
			$entry->addLink(
				$app_root.'/item/'.$this->p_collection_ascii_id.'/'.$this->serial_number.'.json',
				'http://daseproject.org/relation/edit','application/json');
			$entry->addLink(
				$app_root.'/collection/'.$this->p_collection_ascii_id.'/service',
				'service','application/atomsvc+xml');
			$entry->addLink(
				$app_root.'/collection/'.$this->p_collection_ascii_id.'/attributes.json',
				'http://daseproject.org/relation/attributes',
				'application/json');
		} else {
			$entry->addLink(
				$app_root.'/item_type/'.$this->p_collection_ascii_id.'/'.$type->ascii_id.'/item/'.$this->serial_number.'.atom',
				'edit','application/atom+xml');
			$entry->addLink(
				$app_root.'/item_type/'.$this->p_collection_ascii_id.'/'.$type->ascii_id.'/item/'.$this->serial_number.'.json',
				'http://daseproject.org/relation/edit','application/json');
			$entry->addLink(
				$app_root.'/item_type/'.$this->p_collection_ascii_id.'/'.$type->ascii_id.'/item/'.$this->serial_number.'/content',
				'http://daseproject.org/relation/edit-content');
			$entry->addLink(
				$app_root.'/item_type/'.$this->p_collection_ascii_id.'/'.$type->ascii_id.'/service',
				'service','application/atomsvc+xml','',$type->name.' Item Type Service Doc' );
			$entry->addLink(
				$app_root.'/item_type/'.$this->p_collection_ascii_id.'/'.$type->ascii_id.'/attributes.json',
				'http://daseproject.org/relation/attributes','application/json','',$type->name.' Attributes' );
		}

		/* threading extension */

		$replies = $entry->addLink($app_root.'/item/'.$this->p_collection_ascii_id.'/'.$this->serial_number.'/comments','replies' );
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
		$entry->addCategory($this->p_collection_ascii_id,
			'http://daseproject.org/category/collection');
		$entry->addCategory($this->id,'http://daseproject.org/category/item_id');
		$entry->addCategory($this->serial_number,'http://daseproject.org/category/serial_number');

		if ($this->status) {
			$entry->addCategory($this->status,'http://daseproject.org/category/status');
		} else {
			$entry->addCategory('public','http://daseproject.org/category/status');
		}

		/* categories (or links!) for metadata */

		foreach ($this->getRawMetadata() as $row) {
			if ($row['url']) { //create metadata LINK
				$entry->addLink(
					$row['url'],
					'http://daseproject.org/relation/metadata/'.
					$this->p_collection_ascii_id.'/'.$row['ascii_id'],
					'application/atom+xml',
					'',
					$row['value_text']
				);
			} else { //create metadata CATEGORY
				if (0 == $row['collection_id']) {
					$meta = $entry->addCategory(
						$row['ascii_id'],'http://daseproject.org/category/admin_metadata',
						$row['attribute_name'],$row['value_text']);
				} else {
					if ($row['is_public']) {
						$meta = $entry->addCategory(
							$row['ascii_id'],'http://daseproject.org/category/metadata',
							$row['attribute_name'],$row['value_text']);
						$meta->setAttributeNS($d,'d:edit-id',$app_root.'/item/'.$this->p_collection_ascii_id.'/'.$this->serial_number.'/metadata/'.$row['id']);
					} else {
						$meta = $entry->addCategory(
							$row['ascii_id'],'http://daseproject.org/category/private_metadata',
							$row['attribute_name'],$row['value_text']);
						$meta->setAttributeNS($d,'d:edit-id',$app_root.'/item/'.$this->p_collection_ascii_id.'/'.$this->serial_number.'/metadata/'.$row['id']);
					}
					if ('title' == $row['ascii_id'] || 'Title' == $row['attribute_name']) {
						$entry->setTitle($row['value_text']);
					}
					if ('rights' == $row['ascii_id']) {
						$entry->setRights($row['value_text']);
					}
				}
			}
		}

		//this will only "take" if there is not already a title
		$entry->setTitle($this->serial_number);

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
		$thumb_url = $this->getMediaUrl('thumbnail');
		if ($thumb_url) {
			$entry->setThumbnail($app_root.$thumb_url);	
		}

		/* enclosure */

		$enc = $this->getEnclosure();
		if ($enc) {
			$entry->addLink($app_root.$this->getMediaUrl($enc->size),'enclosure',$enc->mime_type,$enc->file_size);
		}

		/* edit-media link */

		$entry->addLink($this->getEditMediaUrl($app_root),'edit-media');

		/* media rss ext */

		foreach ($this->getMedia() as $med) {
			if ('thumbnail' == $med->size) {
				$media_thumbnail = $entry->addElement('media:thumbnail','',Dase_Atom::$ns['media']);
				$media_thumbnail->setAttribute('url',$med->getLink($app_root));
				$media_thumbnail->setAttribute('width',$med->width);
				$media_thumbnail->setAttribute('height',$med->height);
			} else {
				$media_content = $entry->addElement('media:content','',Dase_Atom::$ns['media']);
				$media_content->setAttribute('url',$med->getLink($app_root));
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

	function injectAtomFeedData(Dase_Atom_Feed $feed,$app_root)
	{
		if (!$this->id) { return false; }
		$c = $this->getCollection();
		if (is_numeric($this->updated)) {
			$updated = date(DATE_ATOM,$this->updated);
		} else {
			$updated = $this->updated;
		}
		$feed->setUpdated($updated);
		$feed->setTitle($this->getTitle());
		$feed->setId('tag:daseproject.org,2008:'.Dase_Util::getUniqueName());
		$feed->addLink($app_root.'/item/'.$this->p_collection_ascii_id.'/'.$this->serial_number.'.atom','self' );
		$feed->addAuthor();
		return $feed;
	}

	function asAtom($app_root)
	{
		$feed = new Dase_Atom_Feed;
		$this->injectAtomFeedData($feed,$app_root);
		$feed->setFeedType('item');
		//todo: this needs to be passed in?
		$feed->addCategory('browse',"http://daseproject.org/category/tag_type",'browse');
		$entry = $feed->addItemEntry($this,$app_root); //checks cache 
		//for single item view, add collection name as cat label
		$collection = $this->getCollection();
		$coll_cat = $entry->getCategoryNode('http://daseproject.org/category/collection',$collection->ascii_id);
		$coll_cat->setAttribute('label',$collection->collection_name);
		//add comments
		foreach ($this->getComments() as $comment) {
			$comment_entry = $feed->addEntry('comment');
			$comment->injectAtomEntryData($comment_entry,$app_root);
		}
		return $feed->asXml();
	}

	function asAtomEntry($app_root="{APP_ROOT}")
	{
		$atom = Dase_DBO_ItemAsAtom::getByItem($this);
		if (!$atom) {
			$atom = $this->saveAtom();
		}
		$dom = new DOMDocument('1.0','utf-8');
		$dom->loadXml($atom->getConvertedXml($app_root));
		$e = $dom->getElementsByTagNameNS(Dase_Atom::$ns['atom'],'entry');
		$root = $e->item(0);
		$entry = new Dase_Atom_Entry_Item($dom,$root);
		//for single item view, add collection name as cat label
		$collection = $this->getCollection();
		$coll_cat = $entry->getCategoryNode('http://daseproject.org/category/collection',$collection->ascii_id);
		$coll_cat->setAttribute('label',$collection->collection_name);
		return $entry->asXml();
	}

	/** experimental */
	function asAtomJson($app_root)
	{
		$entry = new Dase_Atom_Entry;
		$this->injectAtomEntryData($entry,$app_root);
		return $entry->asJson();
	}

	function mediaAsAtomFeed($app_root) 
	{
		$feed = new Dase_Atom_Feed;
		$this->injectAtomFeedData($feed,$app_root);
		foreach ($this->getMedia('updated DESC') as $m) {
			$entry = $feed->addEntry();
			$m->injectAtomEntryData($entry,$app_root);
		}
		return $feed->asXml();
	}	

	public function getUrl($app_root) 
	{
		return $app_root.'/item/'.$this->p_collection_ascii_id.'/'.$this->serial_number;
	}

	public function getUnique() 
	{
		return $this->p_collection_ascii_id.'/'.$this->serial_number;
	}

	public function getEditMediaUrl($app_root='')
	{
		return $app_root.'/media/'.$this->p_collection_ascii_id.'/'.$this->serial_number;
	}

	public function getAtomPubServiceDoc($app_root) {
		$c = $this->getCollection();
		$app = new Dase_Atom_Service;
		$workspace = $app->addWorkspace($c->collection_name.' Item '.$this->serial_number.' Workspace');
		$media_coll = $workspace->addCollection($app_root.'/item/'.$this->p_collection_ascii_id.'/'.$this->serial_number.'/media.atom',$c->collection_name.' Item '.$this->serial_number.' Media'); 
		foreach(Dase_Media::getAcceptedTypes() as $type) {
			$media_coll->addAccept($type);
		}
		$comments_coll = $workspace->addCollection($app_root.'/item/'.$this->p_collection_ascii_id.'/'.$this->serial_number.'/comments.atom',$c->collection_name.' Item '.$this->serial_number.' Comments'); 
		$comments_coll->addAccept('text/plain');
		$comments_coll->addAccept('text/html');
		$comments_coll->addAccept('application/xhtml+xml');
		return $app->asXml();
	}

	public function asArray($app_root)
	{
		$j = array();
		$item_array['serial_number'] = $this->serial_number;
		$item_array['created'] = $this->created;
		$item_array['updated'] = $this->updated;
		$item_array['collection'] = $this->p_collection_ascii_id;
		$item_array['edit'] = $app_root.'/item/'.$this->p_collection_ascii_id.'/'.$this->serial_number.'.atom';
		$item_array['metadata'] = array();
		foreach ($this->getMetadata($app_root) as $row) {
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
			$this->p_collection_ascii_id.'/'.$m->size.'/'.$m->filename;
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

	public function asJson($app_root)
	{
		return Dase_Json::get($this->asArray($app_root),true);
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
		$db = $this->db;
		$contents = new Dase_DBO_Content($db);
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
		$db = $this->db;
		$comments = new Dase_DBO_Comment($db);
		$comments->item_id = $this->id;
		return $comments->findCount();
	}

	public function getCommentsUpdated()
	{
		$db = $this->db;
		$comments = new Dase_DBO_Comment($db);
		$comments->item_id = $this->id;
		$comments->orderBy('updated DESC');
		$latest = $comments->findOne();
		return $latest->updated;
	}

	public function getComments($eid='')
	{
		$db = $this->db;
		$comments = new Dase_DBO_Comment($db);
		$comments->item_id = $this->id;
		if ($eid) {
			$comments->updated_by_eid = $eid;
		}
		$comments->orderBy('updated DESC');
		return $comments->find();
	}

	public function getCommentsJson($app_root,$eid='')
	{
		$comments = array();
		foreach ($this->getComments($eid) as $c_obj) {
			$c['id'] = $c_obj->id;
			//$c['updated'] = $c_obj->updated;
			$c['updated'] = date('D M j, Y \a\t g:ia',strtotime($c_obj->updated));
			$c['eid'] = $c_obj->updated_by_eid;
			$c['text'] = $c_obj->text;
			$c['url'] = $this->getUrl($app_root).'/comments/'.$c_obj->id;
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
		$content = new Dase_DBO_Content($this->db);
		$content->item_id = $this->id;
		//todo: security! filter input....
		$content->text = $text;
		$content->type = $type;
		$content->p_collection_ascii_id = $this->p_collection_ascii_id;
		$content->p_serial_number = $this->serial_number;
		$content->updated = date(DATE_ATOM);
		$content->updated_by_eid = $eid;
		$res = $content->insert();
		$this->saveAtom();
		return $res;
	}

	public function addComment($text,$eid)
	{
		$note = new Dase_DBO_Comment($this->db);
		$note->item_id = $this->id;
		//todo: security! filter input....
		$note->text = $text;
		$note->p_collection_ascii_id = $this->p_collection_ascii_id;
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
		$tag_item = new Dase_DBO_TagItem($this->db);
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

	public static function sortIdArrayByUpdated($db,$item_ids)
	{
		$sortable_array = array();
		$prefix = $db->table_prefix;
		$dbh = $db->getDbh();
		$sql = "
			SELECT updated 
			FROM {$prefix}item i
			WHERE i.id = ? 
			";
		$sth = $dbh->prepare($sql);
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

	public static function sortIdArray($db,$sort,$item_ids)
	{
		$sortable_array = array();
		$test_att = new Dase_DBO_Attribute($db);
		$test_att->ascii_id = $sort;
		if (!$test_att->findOne()) {
			return $item_ids;
		}
		$prefix = $db->table_prefix;
		$dbh = $db->getDbh();
		$sql = "
			SELECT v.value_text
			FROM {$prefix}attribute a, {$prefix}value v
			WHERE v.item_id = ?
			AND v.attribute_id = a.id
			AND a.ascii_id = ?
			LIMIT 1
			";
		$sth = $dbh->prepare($sql);
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
	public function expireCaches($cache)
	{
		//more will (perhaps) go here
		//
		// attributes json (includes tallies)
		$cache_id = "get|collection/".$this->p_collection_ascii_id."/attributes/public/tallies|json|cache_buster=stripped&format=json";
		$cache->expire($cache_id);
	
	}
}
