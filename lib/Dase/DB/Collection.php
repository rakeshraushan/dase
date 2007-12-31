<?php

require_once 'Dase/DB/Autogen/Collection.php';

class Dase_DB_Collection extends Dase_DB_Autogen_Collection implements Dase_CollectionInterface
{
	public $item_count;

	public static function get($ascii_id) {
		$c = new Dase_DB_Collection;
		$c->ascii_id = $ascii_id;
		return($c->findOne());
	}

	function asAtom() {
		$feed = new SimpleXMLElement('<feed xmlns="http://www.w3.org/2005/Atom"/>');
		$feed->addChild('title',htmlentities($this->collection_name));
		if ($this->description) {
			$feed->addChild('subtitle',$this->description);
		}
		$ascii_id_category = $feed->addChild('category');
		$ascii_id_category->addAttribute('scheme',"http://daseproject.org/category/collection/ascii_id");
		$ascii_id_category->addAttribute('term',$this->ascii_id);
		$item_count_category = $feed->addChild('category');
		$item_count_category->addAttribute('scheme',"http://daseproject.org/category/collection/item_count");
		$item_count_category->addAttribute('term',$this->getItemCount());
		$feed->addChild('id',APP_ROOT . '/' . $this->ascii_id);
		$feed->addChild('updated',$this->created);
		$author = $feed->addChild('author');
		$author->addChild('name','DASe (Digital Archive Services)');
		$author->addChild('uri','http://daseproject.org');
		$self_link = $feed->addChild('link');
		$self_link->addAttribute('rel',"self");
		$self_link->addAttribute('href',APP_ROOT . '/atom/' . $this->ascii_id);
		$alt_link = $feed->addChild('link');
		$alt_link->addAttribute('rel',"alternate");
		$alt_link->addAttribute('href',APP_ROOT . '/' . $this->ascii_id);
		//format output
		$dom_sxe = dom_import_simplexml($feed);
		$dom = new DOMDocument('1.0');
		$dom_sxe = $dom->importNode($dom_sxe, true);
		$dom->appendChild($dom_sxe);
		$dom->formatOutput = true;
		return $dom->saveXML();
	}

	static function listAsAtom($public_only = false) {
		$c = new Dase_DB_Collection;
		$c->orderBy('collection_name');
		if ($public_only) {
			$c->is_public = 1;
			$cs = $c->find();
		} else {
			$cs = $c->getAll();
		}
		$feed = new Dase_Atom_Feed;
		$feed->setTitle('DASe Collections');
		$feed->setId(APP_ROOT);
		$feed->setUpdated(Dase_DB_Collection::getLastCreated());
		$feed->addAuthor('DASe (Digital Archive Services)','http://daseproject.org');
		$feed->addLink(APP_ROOT.'/atom','self');
		foreach ($cs as $coll) {
			$entry = $feed->addEntry();
			$entry->setTitle($coll->collection_name);
			$entry->setContent($coll->ascii_id);
			$entry->setId(APP_ROOT . '/' . $coll->ascii_id . '/');
			$entry->setUpdated($coll->created);
			$entry->addLink(APP_ROOT.'/atom/'.$coll->ascii_id.'/','self');
			$entry->addLink(APP_ROOT.'/'.$coll->ascii_id.'/','alternate');
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
		foreach ($c->getAll() as $coll) {
			$iter = $coll->getIterator();
			foreach ($iter as $field => $value) {
				$coll_hash[$field] = $value;
			}
			$hash[$coll->id] = $coll_hash;
		}
		return $hash;
	}

	function getAttributes($sort = null) {
		$att = new Dase_DB_Attribute;
		$att->collection_id = $this->id;
		if ($sort) {
			$att->orderBy($sort);
		} else {
			$att->orderBy('sort_order');
		}
		return $att->find();
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
		$type = new Dase_DB_ItemType;
		$type->collection_id = $this->id;
		$type->orderBy('name');
		return $type->find();
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
		$item->orderBy('last_update DESC');
		$item->setLimit(1);
		$item->findOne();
		return $item->last_update;
	}

	public static function getId($ascii_id) {
		$db = Dase_DB::get();
		$sth = $db->prepare("SELECT id from collection WHERE ascii_id = ?");
		$sth->execute(array($ascii_id));
		return $sth->fetchColumn();
	}
}
