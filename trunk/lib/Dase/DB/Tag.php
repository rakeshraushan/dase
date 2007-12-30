<?php

require_once 'Dase/DB/Autogen/Tag.php';

class Dase_DB_Tag extends Dase_DB_Autogen_Tag 
{
	private $type;
	private $user;

	public static function getByUser($user) {
		//$tag = new Dase_DB_Tag;
		//$tag->dase_user_id = $user->id;
		//return $tag->findAll();
		$db = Dase_DB::get();
		$sql = "
			SELECT t.id,t.ascii_id,t.name,t.tag_type_id,count(ti.id)
			FROM tag t , tag_item ti
			WHERE t.id = ti.tag_id
			AND t.dase_user_id = ?
			GROUP BY t.id,t.ascii_id,t.name,t.tag_type_id
			UNION
			SELECT t.id,t.ascii_id,t.name,t.tag_type_id,0
			FROM tag t 
			WHERE NOT EXISTS(SELECT * FROM tag_item ti WHERE ti.tag_id = t.id)
			AND t.dase_user_id = ?
			";
		$sth = $db->prepare($sql);
		$sth->execute(array($user->id,$user->id));
		return $sth->fetchAll();
	}

	function getItemCount() {
		$db = Dase_DB::get();
		$sql = "
			SELECT count(*)
			FROM tag_item 
			where tag_id = ?
			";
		$st = $db->prepare($sql);
		$st->execute(array($this->id));
		$this->item_count = $st->fetchColumn();
		return $this->item_count;
	}

	function getUpdated() {
		$tag_item = new Dase_DB_TagItem;
		$tag_item->tag_id = $this->id;
		$tag_item->orderBy('updated DESC');
		$tag_item->findOne();
		return $tag_item->updated;
	}

	function getTagItems() {
		$item_ids = array();
		$_tag_item = new Dase_DB_TagItem;
		$_tag_item->tag_id = $this->id;
		foreach ($_tag_item->findAll() as $row) {
			$tag_item = new Dase_DB_TagItem($row);
			$tag_items[] = $tag_item;
		}
		return $tag_items;
	}

	function getType() {
		$type = new Dase_DB_TagType;
		$this->type = $type->load($this->tag_type_id);
		return $this->type;
	}

	function getUser() {
		$user = new Dase_DB_DaseUser;
		$this->user = $user->load($this->dase_user_id);
		return $this->user;
	}

	function asAtom() {
		$this->type || $this->getType(); 
		$this->user || $this->getUser(); 
		$feed = new Dase_Atom_Feed;
		$feed->setTitle($this->name);
		if ($this->description) {
			$feed->setSubtitle($this->description);
		}
		$feed->setId(APP_ROOT . '/user/'. $this->user->eid . '/tag/' . $this->ascii_id);
		$feed->setUpdated($this->getUpdated());
		$feed->addAuthor($this->user->eid);
		$feed->addLink(APP_ROOT . '/atom/user/' . $this->user->eid . '/tag/' . $this->ascii_id . '/','self');
		$feed->addCategory($this->getType()->ascii_id,"http://daseproject.org/category/tag_type",$this->type->name);
		if ($this->is_public) {
			$pub = "public";
		} else {
			$pub = "private";
		}
		$feed->addCategory($pub,"http://daseproject.org/category/visibility");
		$feed->addCategory($this->background,"http://daseproject.org/category/tag_background");

		/*  TO DO categories: admin_coll_id, updated, created, master_item, etc */
		foreach($this->getTagItems() as $tag_item) {
			$entry = $feed->addEntry();
			$tag_item->getItem()->injectAtomEntryData($entry);
			/* WORK ON SOURCE !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
				$source = $sx->addChild('source');
			$source->addChild('title',htmlentities($this->item->collection_name));
			if (is_numeric($this->item->updated)) {
				$updated = date(DATE_ATOM,$this->item->updated);
			} else {
				$updated = $this->item->updated;
			}

			$source->addChild('updated',$updated);
			$source->addChild('id',APP_ROOT . '/collection/'. substr($this->item->collection_ascii_id,0,-11));
			 */
		}
		return $feed->asXml();
	}
}
