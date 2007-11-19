<?php

require_once 'Dase/DB/Autogen/Value.php';

class Dase_DB_Value extends Dase_DB_Autogen_Value 
{
	public $attribute = null;
	public $attribute_name;
	public $attribute_ascii_id;

	//this might need renaming????????
	function getAttribute() {
		$a = new Dase_DB_Attribute();
		$a->load($this->attribute_id);	
		$this->attribute_name = $a->attribute_name;
		$this->attribute_ascii_id = $a->ascii_id;
		$this->attribute = $a;
		return $a;
	}

	public static function getValueTextByHash($coll,$md5) {
		//let's assume md5 is unique enough
		$v = new Dase_DB_Value;
		$v->value_text_md5 = $md5;
		return $v->findOne()->value_text;
	}

	function asSimpleXml() {
		$this->getAttributeName();
		$sx = new SimpleXMLElement("<value/>");
		foreach($this as $k => $v) {
			if ($v) {
				$sx->addAttribute($k,$v);
			}
		}
		$node1 = dom_import_simplexml($sx);
		$node1->appendChild(new DOMText($this->attribute_name . " : " . $this->value_text));
		return $sx;
	}

	function resultSetAsSimpleXml() {
		//BEWARE: this is a bit messy/unintuitive
		//it looks first to see if item_id is set
		//else it looks to see if attribute_id is set
		if ($this->item_id) {
			$cond = " AND v.item_id = ?";
			$params[] = $this->item_id;
		} elseif ($this->attribute_id) {
			$cond = " AND v.attribute_id = ?";
			$params[] = $this->attribute_id;
		} else {
			throw new Exception('must specify item_id OR attribute_id'); 
		}
		$sql = "
			SELECT v.value_text, v.value_text_md5, a.attribute_name, a.ascii_id as attribute_ascii_id
			FROM value v, attribute a
			WHERE v.attribute_id = a.id
			$cond
			";	
		$csx = new SimpleXMLElement("<metadata_set/>");
		foreach($this->query($sql,$params) as $row) {
			$new = $csx->addChild('metadata');
			foreach($row as $k => $v) {
				if ($v) {
					$new->addAttribute($k,$v);
				}
			}
			$node1 = dom_import_simplexml($new);
			$node1->appendChild(new DOMText($row['attribute_name'] . " : " . $row['value_text']));
		}
		return $csx;
	}
}
