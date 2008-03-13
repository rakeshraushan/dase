<?php

require_once 'Dase/DB/Autogen/Collection.php';

class Dase_DB_Collection extends Dase_DB_Autogen_Collection
{
	public $item_count;

	public static function get($ascii_id) {
		$c = new Dase_DB_Collection;
		$c->ascii_id = $ascii_id;
		return($c->findOne());
	}

	public function asSimpleXml() {
		$sx = simplexml_load_string("<collection/>");
		foreach($this as $k => $v) {
			$sx->addChild($k,htmlspecialchars($v));
		}
		$sx->addChild('id',$this->id);
		return $sx;
	}

	function asAtom() {
		$feed = new Dase_Atom_Feed;
		$feed->setTitle($this->collection_name);
		if ($this->description) {
			$feed->setSubtitle($this->description);
		}
		$feed->setUpdated($this->getLastUpdated());
		$feed->addCategory($this->ascii_id,"http://daseproject.org/category/collection/ascii_id");
		$feed->addCategory($this->getItemCount(),"http://daseproject.org/category/collection/item_count");
		$feed->setId(APP_ROOT . '/' . $this->ascii_id);
		$feed->addAuthor();
		$feed->addLink(APP_ROOT.'/atom/collection/'.$this->ascii_id,'self');
		$feed->addLink(APP_ROOT.'/collection/'.$this->ascii_id,'alternate');
		return $feed->asXml();
	}

	static function listAsAtom($public_only = false) {
		$c = new Dase_DB_Collection;
		$c->orderBy('collection_name');
		if ($public_only) {
			$c->is_public = 1;
		} 
		$cs = $c->find();
		$feed = new Dase_Atom_Feed;
		$feed->setTitle('DASe Collections');
		$feed->setId(APP_ROOT);
		$feed->setUpdated(Dase_DB_Collection::getLastCreated());
		$feed->addAuthor('DASe (Digital Archive Services)','http://daseproject.org');
		$feed->addLink(APP_ROOT.'/atom','self');
		foreach ($cs as $coll) {
			$entry = $feed->addEntry();
			$entry->setTitle($coll->collection_name);
			$entry->setContent(str_replace('_collection','',$coll->ascii_id));
			$entry->setId(APP_ROOT . '/' . $coll->ascii_id . '/');
			$entry->setUpdated($coll->created);
			$entry->addLink(APP_ROOT.'/atom/collection/'.$coll->ascii_id.'/','self');
			$entry->addLink(APP_ROOT.'/collection/'.$coll->ascii_id.'/','alternate');
			if ($coll->is_public) {
				$pub = "public";
			} else {
				$pub = "private";
			}
			$entry->addCategory($pub,"http://daseproject.org/category/visibility");
		}
		return $feed->asXML();
	}

	static function getLastCreated() {
		$db = Dase_DB::get();
		$sql = "
			SELECT created
			FROM collection
			ORDER BY created DESC
			";
		$st = $db->prepare($sql);
		$st->execute();
		return $st->fetchColumn();
	}

	static function getLookupArray() {
		$hash = array();
		$c = new Dase_DB_Collection;
		foreach ($c->find() as $coll) {
			$iter = $coll->getIterator();
			foreach ($iter as $field => $value) {
				$coll_hash[$field] = $value;
			}
			$hash[$coll->id] = $coll_hash;
		}
		return $hash;
	}

	function getManagers() {
		//note: this returns an array of arrays
		//NOT an array of manager objects
		$db = Dase_DB::get();
		$sql = "
			SELECT m.dase_user_eid,m.auth_level,m.expiration,m.created,u.name 
			FROM collection_manager m,dase_user u 
			WHERE m.collection_ascii_id = ?
			AND m.dase_user_eid = u.eid
			ORDER BY m.dase_user_eid";
		$sth = $db->prepare($sql);
		$sth->setFetchMode(PDO::FETCH_ASSOC);
		$sth->execute(array($this->ascii_id));
		return $sth;
	}

	function getAttributes($sort = 'sort_order') {
		$att = new Dase_DB_Attribute;
		$att->collection_id = $this->id;
		$att->orderBy($sort);
		return $att->find();
	}

	function getAttributesData() {
		$att = new Dase_DB_Attribute;
		$cols = Dase_DB::listColumns('attribute');
		$sql = "
			SELECT *
			FROM attribute
			WHERE collection_id = ?
			ORDER BY sort_order
			";
		$db = Dase_DB::get();
		$sth = $db->prepare($sql);
		$sth->setFetchMode(PDO::FETCH_ASSOC);
		$sth->execute(array($this->id));
		return $sth->fetchAll();
	}

	function changeAttributeSort($att_ascii_id,$new_so) {
		$att_ascii_id_array = array();
		$db = Dase_DB::get();
		$sql = "
			SELECT ascii_id 
			FROM attribute
			WHERE collection_id = ?
			ORDER BY sort_order";
		$sth = $db->prepare($sql);
		$sth->setFetchMode(PDO::FETCH_ASSOC);
		$sth->execute(array($this->id));
		while ($row = $sth->fetch()) {
			if ($att_ascii_id != $row['ascii_id']) {
				$att_ascii_id_array[] = $row['ascii_id'];
			}
		} 
		array_splice($att_ascii_id_array,$new_so-1,0,$att_ascii_id);
		$sql = "
			UPDATE attribute
			SET sort_order = ?,
			updated = ?
			WHERE ascii_id = ?
			AND collection_id = ?";
		$sth = $db->prepare($sql);
		$so = 1;
		foreach ($att_ascii_id_array as $ascii) {
			$now = date(DATE_ATOM);
			$sth->execute(array($so,$now,$ascii,$this->id));
			$so++;
		}
	}

	function getAdminAttributes() {
		$att = new Dase_DB_Attribute;
		$att->collection_id = 0;
		$att->orderBy('sort_order');
		return $att->find();
	}

	function getItemCount() {
		$db = Dase_DB::get();
		$sql = "
			SELECT count(item.id) as count
			FROM item
			where collection_id = ?
			";
		$st = $db->prepare($sql);
		$st->execute(array($this->id));
		$this->item_count = $st->fetchColumn();
		return $this->item_count;
	}

	function getItems() {
		$item = new Dase_DB_Item;
		$item->collection_id = $this->id;
		return $item->find();
	}

	function getItemTypes() {
		$types = new Dase_DB_ItemType;
		$types->collection_id = $this->id;
		$types->orderBy('name');
		return $types->find();
	}

	function getItemsByAttVal($att_ascii_id,$value_text,$substr = false) {
		$a = new Dase_DB_Attribute;
		$a->ascii_id = $att_ascii_id;
		$a->collection_id = $this->id;
		$a->findOne();
		$v = new Dase_DB_Value;
		$v->attribute_id = $a->id;
		if ($substr) {
			$v->addWhere('value_text',"%$value_text%",'like');
		} else {
			$v->value_text = $value_text;
		}
		$items = array();
		foreach ($v->find() as $val) {
			$it = new Dase_DB_Item;
			$it->load($val->item_id);
			$items[] = $it;
		}
		return $items;
	}

	public function buildSearchIndex() {
		$db = Dase_DB::get();
		$db->query("DELETE FROM search_table WHERE collection_id = $this->id");
		$db->query("DELETE FROM admin_search_table WHERE collection_id = $this->id");
		$items = new Dase_DB_Item;
		$items->collection_id = $this->id;
		foreach ($items->find() as $item) {
			//search table
			$composite_value_text = '';
			//NOTE: '= true' works for mysql AND postgres!
			$sql = "
				SELECT value_text
				FROM value
				WHERE item_id = ?
				AND value.attribute_id in (SELECT id FROM attribute where in_basic_search = true)
				";
			$st = $db->prepare($sql);
			$st->execute(array($item->id));
			while ($value_text = $st->fetchColumn()) {
				$composite_value_text .= $value_text . " ";
			}
			$search_table = new Dase_DB_SearchTable;
			$search_table->value_text = $composite_value_text;
			$search_table->item_id = $item->id;
			$search_table->collection_id = $this->id;
			$search_table->insert();

			//admin search table
			$composite_value_text = '';
			$sql = "
				SELECT value_text
				FROM value
				WHERE item_id = ?
				";
			$st = $db->prepare($sql);
			$st->execute(array($item->id));
			while ($value_text = $st->fetchColumn()) {
				$composite_value_text .= $value_text . " ";
			}
			$search_table = new Dase_DB_AdminSearchTable;
			$search_table->value_text = $composite_value_text;
			$search_table->item_id = $item->id;
			$search_table->collection_id = $this->id;
			$search_table->insert();
		}
		return true;
	}

	function createNewItem($serial_number = null) {
		$item = new Dase_DB_Item;
		$item->collection_id = $this->id;
		if ($serial_number) {
			$item->serial_number = $serial_number;
			if ($item->findOne()) {
				throw new Exception('duplicate serial number!');
				return;
			}
			$item->status_id = 0;
			$item->item_type_id = 0;
			$item->insert();
			return $item;
		} else {
			$item->status_id = 0;
			$item->item_type_id = 0;
			$item->insert();
			$item->serial_number = sprintf("%09d",$item->id);
			$item->update();
			return $item;
		}
	}

	function getLastUpdated($id = '') {
		$id = $this->id ? $this->id : $id;
		$item = new Dase_DB_Item;
		$item->collection_id = $id;
		$item->orderBy('updated DESC');
		$item->setLimit(1);
		$item->findOne();
		return $item->updated;
	}

	function staticGetLastUpdated($id) {
		$item = new Dase_DB_Item;
		$item->collection_id = $id;
		$item->orderBy('updated DESC');
		$item->setLimit(1);
		$item->findOne();
		return $item->updated;
	}

	public static function getId($ascii_id) {
		$db = Dase_DB::get();
		$sth = $db->prepare("SELECT id from collection WHERE ascii_id = ?");
		$sth->execute(array($ascii_id));
		return $sth->fetchColumn();
	}

	public function getData($select = 'all') {
		$collection_data = array();
		if (('attributes' == $select) || ('all' == $select)) {
			$collection_data['attributes'] = $this->getAttributesData();
		}
		if (('types' == $select) || ('all' == $select)) {
			foreach ($this->getItemTypes() as $type) {
				foreach ($type as $k => $v) {
					$collection_data['item_types'][$type->ascii_id][$k] = $v;
				}
				foreach ($type->getAttributes() as $type_att) {
					$type_att_as_array = array();
					$type_att_as_array['ascii_id'] = $type_att->ascii_id;
					$type_att_as_array['attribute_name'] = $type_att->attribute_name;
					$type_att_as_array['cardinality'] = $type_att->cardinality;
					$type_att_as_array['is_identifier'] = $type_att->is_identifier;
					$collection_data['item_types'][$type->ascii_id]['attributes'][] = $type_att_as_array;
				}
			}
		}
		if (('settings' == $select) || ('all' == $select)) {
			foreach ($this as $k => $v) {
				$collection_data['settings'][$k] = $v;
			}
		}
		if (('managers' == $select) || ('all' == $select)) {
			foreach ($this->getManagers() as $manager) {
			$collection_data['managers'][] = $manager;
			}
		}
		return Dase_Json::get($collection_data);
	}
}
