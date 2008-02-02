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
		if ($collection_ascii_id && $ascii_id) {
			$a = new Dase_DB_Attribute;
			$a->ascii_id = $ascii_id;
			if ('admin_' == substr($ascii_id,0,6)) {
				$a->collection_id = 0;
			} else {
				$a->collection_id = Dase_DB_Collection::get($collection_ascii_id)->id;
			}
			return($a->findOne());
		}
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

	public static function getId($ascii_id) {
		$db = Dase_DB::get();
		$sth = $db->prepare("SELECT id from attribute WHERE ascii_id = ?");
		$sth->execute(array($ascii_id));
		return $sth->fetchColumn();
	}


	/*
	function injectAtomEntryData(Dase_Atom_Entry $entry) {
		$this->collection || $this->getCollection();
		if (is_numeric($this->updated)) {
			$updated = date(DATE_ATOM,$this->updated);
		} else {
			$updated = $this->updated;
		}
		$entry->setTitle($this->attribute_name);
		$entry->setUpdated($updated);
		$entry->setId(APP_ROOT . '/' . $this->collection_ascii_id . '/' . $this->serial_number);
		$entry->addCategory($this->id,'http://daseproject.org/category/item/id');
		$entry->addCategory($this->collection_ascii_id,'http://daseproject.org/category/collection',$this->collection_name);
		if ($this->item_type) {
			$entry->addCategory($this->item_type_ascii,'http://daseproject.org/category/item_type',$this->item_type_label);
		}
		$entry->addLink(APP_ROOT.'/'.$this->collection_ascii_id.'/'.$this->serial_number,'alternate' );
		//switch to the simple xml interface here
		$div = simplexml_import_dom($entry->setContent());
		$div->addAttribute('class',$this->collection_ascii_id);
		$this->thumbnail || $this->getThumbnail();
		$img = $div->addChild('img');
		$img->addAttribute('src',$this->thumbnail_url);
		$img->addAttribute('class','thumbnail');
		$this->viewitem || $this->getViewitem();
		$img = $div->addChild('img');
		$img->addAttribute('src',$this->viewitem_url);
		$img->addAttribute('class','viewitem');
		$div->addChild('p',htmlspecialchars($this->collection->collection_name))->addAttribute('class','collection_name');;
		$dl = $div->addChild('dl');
		$dl->addAttribute('class','metadata');
		foreach ($this->getMetadata() as $row) {
			//note: since this is used in archiving scripts
			//I use getMetadata() rather than getValues() to
			//conserve memory
			$dl->addChild('dt',htmlspecialchars($row['attribute_name']));
			$dd = $dl->addChild('dd',htmlspecialchars($row['value_text']));
			$dd->addAttribute('class',$row['ascii_id']);
		}
		$media_ul = $div->addChild('ul');
		$media_ul->addAttribute('class','media');
		foreach ($this->getMedia() as $med) {
			$media_li = $media_ul->addChild('li');
			$media_li->addAttribute('class',$med->size);
			$a = $media_li->addChild('a', $med->size . " (" . $med->width ."x" .$med->height .")");
			$a->addAttribute('href', APP_ROOT . "/media/" . $this->collection_ascii_id.'/'.$med->size.'/'.$med->filename);
			$a->addAttribute('class',$med->mime_type);
		}
		if ($this->xhtml_content) {
			$content_sx = new SimpleXMLElement($this->xhtml_content);	
			//from http://us.php.net/manual/en/function.simplexml-element-addChild.php
			$node1 = dom_import_simplexml($div);
			$dom_sxe = dom_import_simplexml($content_sx);
			$node2 = $node1->ownerDocument->importNode($dom_sxe, true);
			$node1->appendChild($node2);
		} elseif ($this->text_content) {
			$text = $div->addChild('div',htmlspecialchars($content));
			$text->addAttribute('class','itemContent');
		}
		return $entry;
	}
	 */


}


