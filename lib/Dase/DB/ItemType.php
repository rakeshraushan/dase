<?php

require_once 'Dase/DB/Autogen/ItemType.php';

class Dase_DB_ItemType extends Dase_DB_Autogen_ItemType 
{
	public $attributes;

	function getAttributes() {
		$attributes = array();
		$att_it = new Dase_DB_AttributeItemType;
		$att_it->item_type_id = $this->id;
		foreach($att_it->findAll() as $res) {
			$att = new Dase_DB_Attribute;
			$att->load($res['attribute_id']);
			$att->cardinality = $res['cardinality']; 
			$attributes[] = $att;
		}
		$this->attributes = $attributes;
		return $attributes;
	}

	function asSimpleXml() {
		$sx = new SimpleXMLElement("<item_type/>");
		foreach($this as $k => $v) {
			if ($v) {
				if ('name' == $k) {
					$node1 = dom_import_simplexml($sx);
					$node1->appendChild(new DOMText($v));
				}
				$sx->addAttribute($k,$v);
			}
		}
		return $sx;
	}

	function resultSetAsSimpleXml() {
		$sx = new SimpleXMLElement("<item_types/>");
		foreach($this->findAll() as $row) {
			$new = $sx->addChild('item_type');
			foreach($row as $key => $val) {
				if ($val) {
					$new->addAttribute($key,$val);
					if ('name' == $key) {
						$node1 = dom_import_simplexml($new);
						$node1->appendChild(new DOMText($val));
					}
				}
			}
		}
		return $sx;
	}
}
