<?php

require_once 'Dase/DB/Autogen/Attribute.php';

class Dase_DB_Attribute extends Dase_DB_Autogen_Attribute implements Dase_AttributeInterface
{
	public $cardinality;
	public $collection = null;
	public $display_values = array();
	public $html_input_type = null;

	function getValueCount() {
		if (!$this->id) {
			throw new Exception('attribute not instantiated/loaded'); 
		}
		$db = Dase_DB::get();
		$st = $db->prepare('SELECT count(*) FROM value WHERE attribute_id = ?');
		$st->execute(array($this->id));	
		return $st->fetchColumn();
	}

	function getDisplayValues($coll = null) {
		$admin_sql = '';
		if (!$this->id) {
			throw new Exception('attribute not instantiated/loaded'); 
		}
		$db = Dase_DB::get();
		//presence od collection_id says it is an admin att
		if ($coll) {
			$admin_sql = "AND item_id IN (SELECT id FROM item WHERE collection_id IN (SELECT id FROM collection WHERE ascii_id = '$coll'))";
		}
		$sql = "
			SELECT value_text, value_text_md5, count(value_text)
			FROM value
			WHERE attribute_id = ?
			$admin_sql
			GROUP BY value_text, value_text_md5
			ORDER BY value_text
			";
		$st = $db->prepare($sql);
		$st->execute(array($this->id));
		$display_values_array = array();
		while ($row = $st->fetch()) {
			$display_values_array[] = array(
				'value_text' => $row[0],
				'urlencoded_value_text' => urlencode($row[0]),
				'value_text_md5' => $row[1],
				'tally' => $row[2]
			);
		}
		$this->display_values = $display_values_array;
		return $display_values_array;
	}

	public static function get($collection_ascii_id,$ascii_id) {
		$a = new Dase_DB_Attribute;
		$a->ascii_id = $ascii_id;
		if ('admin_' == substr($ascii_id,0,6)) {
			$a->collection_id = 0;
		} else {
			$a->collection_id = Dase_DB_Collection::get($collection_ascii_id)->id;
		}
		return($a->findOne());
	}

	public static function getAdmin($ascii_id) {
		$a = new Dase_DB_Attribute;
		$a->ascii_id = $ascii_id;
		$a->collection_id = 0;
		return($a->findOne());
	}

	public function getCollection() {
		$c = new Dase_DB_Collection;
		$c->load($this->collection_id);
		$this->collection = $c;
		return $c;
	}

	function getHtmlInputType() {
		$inp = new Dase_DB_HtmlInputType;
		$inp->load($this->html_input_type_id);
		$this->html_input_type = $inp;
		return $inp;
	}

	function findAsXml($serialize = true) {
		$sx = parent::findAsXml(false);
		foreach ($sx->attribute as $att) {
			foreach ($att as $v) {
				if ('attribute_name' == $att) {
					$node1 = dom_import_simplexml($att);
					$node1->appendChild(new DOMText($v));
				}
			}
		}
		if ($serialize) {
			return $sx->asXml();
		} else {
			return $sx;
		}
	}

	function asSimpleXml() {
		$db = Dase_DB::get();
		$sql = "
			SELECT a.attribute_name, a.ascii_id, c.ascii_id as collection,
			a.usage_notes, a.sort_order, a.in_basic_search, a.is_on_list_display,
			a.is_public, h.name as html_input_type, a.atom_element, a.timestamp as updated,
			a.mapped_admin_att_id
			FROM attribute a, collection c, html_input_type h
			WHERE a.collection_id = c.id
			AND h.id = a.html_input_type_id
			AND c.id = ? 
			AND a.ascii_id = ? 
			";
		$sth = $db->prepare($sql);
		$sth->setFetchMode(PDO::FETCH_ASSOC);
		$sth->execute(array($this->collection_id,$this->ascii_id));
		$sx = new SimpleXMLElement("<attribute/>");
		foreach($sth->fetch() as $key => $val) {
			if ('mapped_admin_att_id' == $key) {
				$key = 'admin_equiv';
				$val = $this->getAdminEquiv($val);
			}
			if ($val) {
				$sx->addAttribute($key, $val);
				if ('attribute_name' == $key) {
					$node1 = dom_import_simplexml($sx);
					$node1->appendChild(new DOMText($val));
				}
			}
		}
		return $sx;
	}

	function resultSetAsSimpleXml() {
		$db = Dase_DB::get();
		$sql = "
			SELECT a.attribute_name, a.ascii_id, c.ascii_id as collection,
			a.usage_notes, a.sort_order, a.in_basic_search, a.is_on_list_display,
			a.is_public, h.name as html_input_type, a.atom_element, a.timestamp as updated,
			a.mapped_admin_att_id
			FROM attribute a, collection c, html_input_type h
			WHERE a.collection_id = c.id
			AND h.id = a.html_input_type_id
			AND c.id = ? 
			";
		$sth = $db->prepare($sql);
		$sth->setFetchMode(PDO::FETCH_ASSOC);
		$sth->execute(array($this->collection_id));
		$sx = new SimpleXMLElement("<attributes/>");
		foreach($sth->fetchAll() as $row) {
			$row['admin_equiv'] = $this->getAdminEquiv($row['mapped_admin_att_id']);
			$row['mapped_admin_att_id'] = 0;
			$new = $sx->addChild('attribute');
			foreach($row as $key => $val) {
				if ($val) {
					$new->addAttribute($key, $val);
					if ('attribute_name' == $key) {
						$node1 = dom_import_simplexml($new);
						$node1->appendChild(new DOMText($val));
					}
				}
			}
		}
		return $sx;
	}

	function getAdminEquiv($mapped_id) {
		if ($this->mapped_admin_att_id) {
			$mapped_id = $this->mapped_admin_att_id;
		}
		$aa = new Dase_DB_Attribute;
		if ($aa->load($mapped_id)) {
			return $aa->ascii_id;
		} else {
			return 'none';
		}
	}
}


