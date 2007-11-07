<?php

require_once 'Dase/DB/Autogen/Attribute.php';

class Dase_DB_Attribute extends Dase_DB_Autogen_Attribute 
{
	public $collection = null;
	public $display_values = array();
	public $cardinality;

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

	function getAtom() {
		$this->collection || $this->getCollection();
		$dom = new DOMDocument;
		$feed = $dom->createElement('feed');
		$feed->setAttribute('xmlns','http://www.w3.org/2005/Atom');
		$alt_link = $dom->createElement('link');
		$alt_link->setAttribute('rel','alternate');
		$alt_link->setAttribute('type','application/atom+xml');
		$alt_link->setAttribute('href',APP_ROOT . "/{$this->collection->ascii_id}/att/{$this->ascii_id}");
		$feed->appendChild($alt_link);
		$self_link = $dom->createElement('link');
		$self_link->setAttribute('rel','self');
		$self_link->setAttribute('type','application/atom+xml');
		$self_link->setAttribute('href',APP_ROOT . "/atom/{$this->collection->ascii_id}/att/{$this->ascii_id}");
		$feed->appendChild($self_link);
		$title = $dom->createElement('title');
		$title->appendChild($dom->createTextNode($this->attribute_name));
		$feed->appendChild($title);
		$id = $dom->createElement('id');
		$id->appendChild($dom->createTextNode(APP_ROOT . "/{$this->collection->ascii_id}/att/{$this->ascii_id}"));
		$feed->appendChild($id);
		$author = $dom->createElement('author');
		$name = $dom->createElement('name');
		$name->appendChild($dom->createTextNode('DASe'));
		$author->appendChild($name);
		$feed->appendChild($author);
		$updated = $dom->createElement('updated');
		$updated->appendChild($dom->createTextNode(date('c',$this->timestamp)));
		$feed->appendChild($updated);

		$entry = $dom->createElement('entry');
		$id = $dom->createElement('id');
		$id->appendChild($dom->createTextNode(APP_ROOT . "/{$this->collection->ascii_id}/att/{$this->ascii_id}"));
		$entry->appendChild($id);
		$title = $dom->createElement('title');
		$title->appendChild($dom->createTextNode($this->attribute_name));
		$entry->appendChild($title);
		$updated = $dom->createElement('updated');
		$updated->appendChild($dom->createTextNode(date('c',$this->timestamp)));
		$entry->appendChild($updated);

		if ($this->atom_element) {
			$atom_cat = $dom->createElement('category');
			$atom_cat->setAttribute('term',$this->atom_element);
			$atom_cat->setAttribute('scheme',APP_ROOT . "/categories/attribute/atom-equiv/");
			$atom_cat->setAttribute('label',$this->atom_element);
			$entry->appendChild($atom_cat);
		}

		if ($this->mapped_admin_att_id) {
			$aa = new Dase_DB_Attribute;
			if ($aa->load($this->mapped_admin_att_id)) {
				$mapped_cat = $dom->createElement('category');
				$mapped_cat->setAttribute('term',$aa->ascii_id);
				$mapped_cat->setAttribute('scheme',APP_ROOT . "/categories/attribute/admin-attribute-equiv/");
				$mapped_cat->setAttribute('label',$aa->attribute_name);
				$entry->appendChild($mapped_cat);
			}
		}

		if ($this->html_input_type_id) {
			$inp = new Dase_DB_HtmlInputType;
			if ($inp->load($this->html_input_type_id)) {
				$input_cat = $dom->createElement('category');
				$input_cat->setAttribute('term',$inp->name);
				$input_cat->setAttribute('scheme',APP_ROOT . "/categories/attribute/html-input-type/");
				$input_cat->setAttribute('label','HTML input:' . $inp->name);
				$entry->appendChild($input_cat);
			}
		}

		$sort_cat = $dom->createElement('category');
		$sort_cat->setAttribute('term',$this->sort_order);
		$sort_cat->setAttribute('scheme',APP_ROOT . "/categories/attribute/sort-order/");
		$sort_cat->setAttribute('label','sort:'. $this->sort_order);
		$entry->appendChild($sort_cat);

		if ($this->is_on_list_display) {
			$list_cat = $dom->createElement('category');
			$list_cat->setAttribute('term','on_list_display');
			$list_cat->setAttribute('scheme',APP_ROOT . "/categories/attribute/on-list-display/");
			$list_cat->setAttribute('label','on list display');
			$entry->appendChild($list_cat);
		}

		if ($this->in_basic_search) {
			$basic_cat = $dom->createElement('category');
			$basic_cat->setAttribute('term','in_basic_search');
			$basic_cat->setAttribute('scheme',APP_ROOT . "/categories/attribute/in-basic-search/");
			$basic_cat->setAttribute('label','in basic search');
			$entry->appendChild($basic_cat);
		}

		$pp_cat = $dom->createElement('category');
		$pp_cat->setAttribute('scheme',APP_ROOT . "/categories/attribute/public-private/");
		if ($this->is_public) {
			$pp_cat->setAttribute('term','public');
			$pp_cat->setAttribute('label','public');
		} else {
			$pp_cat->setAttribute('term','private');
			$pp_cat->setAttribute('label','private');
		}
		$entry->appendChild($pp_cat);


		$content = $dom->createElement('content');
		$content->setAttribute('type','xhtml');
		$ns_prefix = substr($this->ascii_id,0,3);
		$div = $dom->createElement('div');
		$div->setAttribute('xmlns',"http://www.w3.org/1999/xhtml");
		$div->setAttribute('xmlns:' . $ns_prefix,APP_ROOT . "/{$this->ascii_id}");

		$content_name = $dom->createElement('p');
		$content_name->appendChild($dom->createTextNode($this->attribute_name));
		$div->appendChild($content_name);

		if ($this->usage_notes) {
			$content_notes = $dom->createElement('p');
			$content_notes->appendChild($dom->createTextNode($this->usage_notes));
			$div->appendChild($content_notes);
		}

		$content->appendChild($div);
		$entry->appendChild($content);

		$xml_link = $dom->createElement('link');
		$xml_link->setAttribute('rel','alternate');
		$xml_link->setAttribute('type','application/xml');
		$xml_link->setAttribute('href',APP_ROOT . "/xml/{$this->ascii_id}/{$this->ascii_id}");
		$entry->appendChild($xml_link);
		$feed->appendChild($entry);
		$dom->appendChild($feed);
		$dom->formatOutput = true;
		return $dom->saveXml();
	}
}

