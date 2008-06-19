<?php

require_once 'Dase/DBO/Autogen/Value.php';

class Dase_DBO_Value extends Dase_DBO_Autogen_Value 
{
	public static function getCount($collection_ascii_id='')
	{
		$db = Dase_DB::get();
		$sql = "
			SELECT count(*) 
			FROM value
			";
		if ($collection_ascii_id) {
			$sql .= "
				, item, collection
				WHERE value.item_id = item.id
				AND collection.id = item.collection_id
				AND collection.ascii_id = ?
				";
			$sth = $db->prepare($sql);
			$sth->execute(array($collection_ascii_id));
		} else {
			$sth = $db->prepare($sql);
			$sth->execute();
		}
		return $sth->fetchColumn();
	}

	/** beware: this could be slow.  use only in batch process */
	public static function persistAll()
	{
		$sql = "
			SELECT c.ascii_id as collection_ascii_id,i.serial_number,a.ascii_id as attribute_ascii_id,v.id
			FROM value v, collection c, attribute a, item i
			WHERE i.id = v.item_id
			AND i.collection_id = c.id
			AND a.id = v.attribute_id
			AND v.p_collection_ascii_id IS NULL
			";
		$i = 0;
		foreach(Dase_DBO::query($sql) as $res) {
			$i++;
			$value = new Dase_DBO_Value;
			$value->load($res->id);
			$value->p_collection_ascii_id = $res->collection_ascii_id;
			$value->p_attribute_ascii_id = $res->attribute_ascii_id;
			$value->p_serial_number = $res->serial_number;
			$value->update();
		}
		return "persisted $i values\n";
	}

	public function persist()
	{	
		$db = Dase_DB::get();
		$sql = "
			SELECT c.ascii_id as collection_ascii_id,i.serial_number,a.ascii_id as attribute_ascii_id,v.id
			FROM value v, collection c, attribute a, item i
			WHERE i.id = v.item_id
			AND i.collection_id = c.id
			AND a.id = v.attribute_id
			AND v.id = ? 
			";
		$sth = $db->prepare($sql);
		$sth->execute(array($this->id));
		$row = $sth->fetch();
		$this->p_collection_ascii_id = $row['collection_ascii_id'];
		$this->p_attribute_ascii_id = $row['attribute_ascii_id'];
		$this->p_serial_number = $row['serial_number'];
		$this->update();
		return $this;
	}
}
