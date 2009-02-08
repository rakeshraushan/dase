<?php

require_once 'Dase/DBO/Autogen/Value.php';

class Dase_DBO_Value extends Dase_DBO_Autogen_Value 
{
	public $attribute =  null;

	public static function getCount($collection_ascii_id='')
	{
		$prefix = Dase_Config::get('table_prefix');
		$db = Dase_DB::get();
		$sql = "
			SELECT count(*) 
			FROM {$prefix}value v
			";
		if ($collection_ascii_id) {
			$sql .= "
				, {$prefix}item i, {$prefix}collection c
				WHERE v.item_id = i.id
				AND c.id = i.collection_id
				AND c.ascii_id = ?
				";
			$sth = $db->prepare($sql);
			$sth->execute(array($collection_ascii_id));
		} else {
			$sth = $db->prepare($sql);
			$sth->execute();
		}
		return $sth->fetchColumn();
	}

	public function getAttribute()
	{
		if (!$this->attribute) {
			$att = new Dase_DBO_Attribute;
			$att->load($this->attribute_id);
			$this->attribute = $att;
		}
		return $this->attribute;
	}

	public static function updateAndFlush($value_id,$value_text)
	{
		$v = new Dase_DBO_Value;
		$v->load($value_id);
		$v->value_text = $value_text;
		$v->update();
		$prefix = Dase_Config::get('table_prefix');

		$sql = "
			DELETE 
			FROM {$prefix}item_as_atom
			WHERE item_id = ?
			";
		$sth = $db->prepare($sql);
		$sth->execute(array($v->item_id));
	}
}

