<?php

require_once 'Dase/DB/Autogen/Tag.php';

class Dase_DB_Tag extends Dase_DB_Autogen_Tag 
{
	public static function getByUser($user) {
		$tag = new Dase_DB_Tag;
		$tag->dase_user_id = $user->id;
		return $tag->findAll();
	}

	function getXml() {
		//merge 3 sets of xml results
		$tag = new Dase_DB_Tag;
		$tag->ascii_id = $this->ascii_id;
		$tag->dase_user_id = $this->dase_user_id;
		$tag_item = new Dase_DB_TagItem;
		$sql = "
			SELECT 
			t.annotation, t.p_collection_ascii_id, 
			t.p_serial_number, t.size, t.sort_order, 
			t.timestamp, m.filename, m.mime_type,
		   	m.width, m.height, m.file_size
			FROM tag_item t, media_file m
			WHERE t.item_id = m.item_id
			AND m.size = t.size
			AND t.tag_id = $this->id
		";	
		$tag_xml = Dase_Util::simplexml_append($tag->findAsXml(false),$tag_item->queryAsXml(false,$sql));
		foreach ($tag_xml->tag_items->tag_item as $tag_item) {
			$tag_item['url'] = APP_ROOT . '/media/' . $tag_item['p_collection_ascii_id'] . '/' . $tag_item['size'] . '/' . $tag_item['filename'];
		}
		return $tag_xml->asXml();
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

	function getItemIds() {
		$item_ids = array();
		$tag_item = new Dase_DB_TagItem;
		$tag_item->tag_id = $this->id;
		foreach ($tag_item->findAll() as $row) {
			$item_ids[] = $row['item_id'];
		}
		return $item_ids;
	}
}
