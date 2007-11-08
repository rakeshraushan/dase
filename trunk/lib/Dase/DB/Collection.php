<?php

require_once 'Dase/DB/Autogen/Collection.php';

class Dase_DB_Collection extends Dase_DB_Autogen_Collection implements Dase_CollectionInterface
{
	public $item_count;
	public $admin_attribute_array;
	public $attribute_array;
	public $item_type_array;

	public static function get($ascii_id) {
		$c = new Dase_DB_Collection;
		$c->ascii_id = $ascii_id;
		return($c->findOne());
	}

	function getXmlArchive($limit = 100000) {
		$admin_atts = $this->getAdminAttributeAsciiIds();
		$writer = new XMLWriter();
		$writer->openMemory();
		$writer->setIndent(true);
		$writer->startDocument('1.0','UTF-8');
		$writer->startElement('archive');
		$writer->writeAttribute('name',$this->collection_name);
		$writer->writeAttribute('ascii_id',$this->ascii_id);
		$writer->writeAttribute('description',$this->description);
		$writer->writeAttribute('is_public',$this->is_public);
		$writer->writeAttribute('updated',$this->getLastUpdated());
		$attribute = new Dase_DB_Attribute;
		$attribute->collection_id = $this->id;
		foreach($attribute->findAll() as $att) {
			$writer->startElement('attribute');
			$writer->writeAttribute('name',$att['attribute_name']);
			$writer->writeAttribute('ascii_id',$att['ascii_id']);
			$writer->writeAttribute('sort_order',$att['sort_order']);
			$writer->writeAttribute('is_public',$att['is_public']);
			if ($att['atom_element']) {
				$writer->writeAttribute('atom_element',$att['atom_element']);
			}
			if ($att['mapped_admin_att_id']) {
				$writer->writeAttribute('mapped_admin_attribute',$admin_atts[$att['mapped_admin_att_id']]);
			}
			$writer->endElement();
		}
		$type = new Dase_DB_ItemType;
		$type->collection_id = $this->id;
		foreach($type->findAll() as $t) {
			$writer->startElement('item_type');
			$writer->writeAttribute('name',$t['name']);
			$writer->writeAttribute('ascii_id',$t['ascii_id']);
			$writer->writeAttribute('description',$t['description']);
			$it_obj = new Dase_DB_ItemType;
			$it_obj->load($t['id']);
			foreach ($it_obj->getAttributes() as $a) {
				$writer->startElement('attribute');
				$writer->writeAttribute('ascii_id',$a->ascii_id);
				$writer->writeAttribute('cardinality',$a->cardinality);
				$writer->endElement();
			}
			$writer->endElement();
		}
		$item = new Dase_DB_Item;
		$item->collection_id = $this->id;
		if ($limit) { //0 means no limit
			$item->setLimit($limit);
		}
		foreach($item->findAll() as $it) {
			$writer->startElement('item');
			$writer->writeAttribute('serial_number',$it['serial_number']);
			$writer->writeAttribute('last_update',$it['last_update']);
			$writer->writeAttribute('created',$it['created']);
			$item_status = Dase_DB_Object::getArray('item_status',$it['status_id']);
			if (isset($item_status['status'])) {
				$writer->writeAttribute('status',$item_status['status']);
			}
			$item_type = Dase_DB_Object::getArray('item_type',$it['item_type_id']);
			if (isset($item_type['ascii_id'])) {
				$writer->writeAttribute('item_type',$item_type['ascii_id']);
			}
			//straight db for 
			//metadata, took 2:40 
			//for 18K records
			$db = Dase_DB::get();
			$sql = "
				SELECT value_text,ascii_id,value_text_md5 
				FROM value, attribute
				WHERE attribute.id = value.attribute_id
				AND value.item_id = {$it['id']}
			";
			$st = $db->query($sql);
			foreach ($st->fetchAll() as $row) {
				$writer->startElement('metadata');
				$writer->writeAttribute('attribute_ascii_id',$row['ascii_id']);
				$writer->writeAttribute('value_text_md5',$row['value_text_md5']);
				$writer->text($row['value_text']);
				$writer->endElement();
			}
			//db_object get too 
			//4:40 for 18K 
			//records
			/*
			$value = new Dase_DB_Value;
			$value->item_id = $it['id'];
			foreach($value->findAll() as $val) {
				$writer->startElement('metadata');
				$att = Dase_DB_Object::getArray('attribute',$val['attribute_id']);
				$writer->writeAttribute('attribute_ascii_id',$att['ascii_id']);
				$writer->text($val['value_text']);
				$writer->endElement();
			}
			 */
			$media_file = new Dase_DB_MediaFile;
			$media_file->item_id = $it['id'];
			foreach($media_file->findAll() as $mf) {
				$writer->startElement('media_file');
				$writer->writeAttribute('filename',$mf['filename']);
				$writer->writeAttribute('file_size',$mf['file_size']);
				$writer->writeAttribute('size',$mf['size']);
				$writer->writeAttribute('mime_type',$mf['mime_type']);
				$writer->writeAttribute('width',$mf['width']);
				$writer->writeAttribute('height',$mf['height']);
				$writer->writeAttribute('id',$mf['id']);
				$writer->endElement();
			}
			$writer->endElement();
		}
		$writer->endElement();
		$writer->endDocument();
		return $writer->flush(true);
	}


	function getXml() {
		//merge 3 sets of xml results
		$coll = new Dase_DB_Collection;
		$coll->ascii_id = $this->ascii_id;
		$attribute = new Dase_DB_Attribute;
		$attribute->collection_id = $this->id;
		$type = new Dase_DB_ItemType;
		$type->collection_id = $this->id;
		$coll_xml = Dase_Util::mergeDbXml(
			Dase_Util::mergeDbXml($coll->findOneAsXml(false),$attribute->findAsXml(false),'attributes'),
			$type->findAsXml(false),
			'item_types');
		$coll_xml['item_count'] = $this->getItemCount();
		return $coll_xml->asXml();
	}

	function getAtom() {
		$dom = new DOMDocument;
		$feed = $dom->createElement('feed');
		$feed->setAttribute('xmlns','http://www.w3.org/2005/Atom');
		$alt_link = $dom->createElement('link');
		$alt_link->setAttribute('rel','alternate');
		$alt_link->setAttribute('type','application/atom+xml');
		$alt_link->setAttribute('href',APP_ROOT . "/{$this->ascii_id}");
		$feed->appendChild($alt_link);
		$self_link = $dom->createElement('link');
		$self_link->setAttribute('rel','self');
		$self_link->setAttribute('type','application/atom+xml');
		$self_link->setAttribute('href',APP_ROOT . "/atom/{$this->ascii_id}");
		$feed->appendChild($self_link);
		$title = $dom->createElement('title');
		$title->appendChild($dom->createTextNode($this->collection_name));
		$feed->appendChild($title);
		$id = $dom->createElement('id');
		$id->appendChild($dom->createTextNode(APP_ROOT . "/{$this->ascii_id}"));
		$feed->appendChild($id);
		$author = $dom->createElement('author');
		$name = $dom->createElement('name');
		$name->appendChild($dom->createTextNode('DASe'));
		$author->appendChild($name);
		$feed->appendChild($author);
		$feed_updated = $dom->createElement('updated');
		$feed->appendChild($feed_updated);

		$biggest_timestamp = 0;
		foreach( $this->getAttributes() as $row ) {
			$att = new Dase_DB_Attribute($row);
			$entry = $dom->createElement('entry');

			$title = $dom->createElement('title');
			$title->appendChild($dom->createTextNode('Attribute: ' .$att->attribute_name));
			$entry->appendChild($title);

			$id = $dom->createElement('id');
			$id->appendChild($dom->createTextNode(APP_ROOT . "/{$this->ascii_id}/att/{$att->ascii_id}"));
			$entry->appendChild($id);

			$cat = $dom->createElement('category');
			$cat->setAttribute('term','attribute');
			$cat->setAttribute('scheme',APP_ROOT . "/categories/");
			$cat->setAttribute('label','Attribute');
			$entry->appendChild($cat);

			if ($att->atom_element) {
				$atom_cat = $dom->createElement('category');
				$atom_cat->setAttribute('term',$att->atom_element);
				$atom_cat->setAttribute('scheme',APP_ROOT . "/categories/attribute/atom-equiv/");
				$atom_cat->setAttribute('label',$att->atom_element);
				$entry->appendChild($atom_cat);
			}

			if ($att->mapped_admin_att_id) {
				$aa = new Dase_DB_Attribute;
				if ($aa->load($att->mapped_admin_att_id)) {
					$mapped_cat = $dom->createElement('category');
					$mapped_cat->setAttribute('term',$aa->ascii_id);
					$mapped_cat->setAttribute('scheme',APP_ROOT . "/categories/attribute/admin-attribute-equiv/");
					$mapped_cat->setAttribute('label',$aa->attribute_name);
					$entry->appendChild($mapped_cat);
				}
			}

			if ($att->html_input_type_id) {
				$inp = new Dase_DB_HtmlInputType;
				if ($inp->load($att->html_input_type_id)) {
					$input_cat = $dom->createElement('category');
					$input_cat->setAttribute('term',$inp->name);
					$input_cat->setAttribute('scheme',APP_ROOT . "/categories/attribute/html-input-type/");
					$input_cat->setAttribute('label','HTML input:' . $inp->name);
					$entry->appendChild($input_cat);
				}
			}

			$sort_cat = $dom->createElement('category');
			$sort_cat->setAttribute('term',$att->sort_order);
			$sort_cat->setAttribute('scheme',APP_ROOT . "/categories/attribute/sort-order/");
			$sort_cat->setAttribute('label','sort:'. $att->sort_order);
			$entry->appendChild($sort_cat);

			if ($att->is_on_list_display) {
				$list_cat = $dom->createElement('category');
				$list_cat->setAttribute('term','on_list_display');
				$list_cat->setAttribute('scheme',APP_ROOT . "/categories/attribute/on-list-display/");
				$list_cat->setAttribute('label','on list display');
				$entry->appendChild($list_cat);
			}

			if ($att->in_basic_search) {
				$basic_cat = $dom->createElement('category');
				$basic_cat->setAttribute('term','in_basic_search');
				$basic_cat->setAttribute('scheme',APP_ROOT . "/categories/attribute/in-basic-search/");
				$basic_cat->setAttribute('label','in basic search');
				$entry->appendChild($basic_cat);
			}

			$pp_cat = $dom->createElement('category');
			$pp_cat->setAttribute('scheme',APP_ROOT . "/categories/attribute/public-private/");
			if ($att->is_public) {
				$pp_cat->setAttribute('term','public');
				$pp_cat->setAttribute('label','public');
			} else {
				$pp_cat->setAttribute('term','private');
				$pp_cat->setAttribute('label','private');
			}
			$entry->appendChild($pp_cat);

			$biggest_timestamp = $att->timestamp >= $biggest_timestamp ? $att->timestamp  : $biggest_timestamp;
			$updated = $dom->createElement('updated');
			$updated->appendChild($dom->createTextNode(date('c',$att->timestamp)));
			$entry->appendChild($updated);

			$content = $dom->createElement('content');
			$content->setAttribute('type','xhtml');
			$ns_prefix = substr($this->ascii_id,0,3);
			$div = $dom->createElement('div');
			$div->setAttribute('xmlns',"http://www.w3.org/1999/xhtml");
			$div->setAttribute('xmlns:' . $ns_prefix,APP_ROOT . "/{$this->ascii_id}");

			$content_name = $dom->createElement('p');
			$content_name->appendChild($dom->createTextNode($att->attribute_name));
			$div->appendChild($content_name);

			if ($att->usage_notes) {
				$content_notes = $dom->createElement('p');
				$content_notes->appendChild($dom->createTextNode($att->usage_notes));
				$div->appendChild($content_notes);
			}

			$content->appendChild($div);
			$entry->appendChild($content);

			$xml_link = $dom->createElement('link');
			$xml_link->setAttribute('rel','alternate');
			$xml_link->setAttribute('type','application/xml');
			$xml_link->setAttribute('href',APP_ROOT . "/html/{$this->ascii_id}/attribute/{$att->ascii_id}");
			$entry->appendChild($xml_link);
			$feed->appendChild($entry);

		}

		foreach( $this->getItemTypes() as $row ) {
			$type = new Dase_DB_ItemType($row);
			$entry = $dom->createElement('entry');

			$title = $dom->createElement('title');
			$title->appendChild($dom->createTextNode('Item Type: ' . $type->name));
			$entry->appendChild($title);

			$id = $dom->createElement('id');
			$id->appendChild($dom->createTextNode(APP_ROOT . "/{$this->ascii_id}/item_type/{$type->ascii_id}"));
			$entry->appendChild($id);

			$cat = $dom->createElement('category');
			$cat->setAttribute('term','item_type');
			$cat->setAttribute('scheme',APP_ROOT . "/categories/");
			$cat->setAttribute('label','Item Type');
			$entry->appendChild($cat);

			/*
			$biggest_timestamp = $att->timestamp >= $biggest_timestamp ? $att->timestamp  : $biggest_timestamp;
			$updated = $dom->createElement('updated');
			$updated->appendChild($dom->createTextNode(date('c',$att->timestamp)));
			$entry->appendChild($updated);
			 */

			$content = $dom->createElement('content');
			$content->setAttribute('type','xhtml');
			$ns_prefix = substr($this->ascii_id,0,3);
			$div = $dom->createElement('div');
			$div->setAttribute('xmlns',"http://www.w3.org/1999/xhtml");
			$div->setAttribute('xmlns:' . $ns_prefix,APP_ROOT . "/{$this->ascii_id}");

			$xoxo = $dom->createElement('ul');
			$name = $dom->createElement('li');
			$name->appendChild($dom->createTextNode($type->name));
			$xoxo->appendChild($name);

			$atts = $type->getAttributes();
			if ($atts) {
				$atts_label = $dom->createElement('li');
				$atts_label->appendChild($dom->createTextNode('Attributes'));
				$xoxo->appendChild($atts_label);
				$atts_list = $dom->createElement('ul');
				foreach ($atts as $att) {
					$att_li = $dom->createElement('li');
					$att_li->appendChild($dom->createTextNode($att->ascii_id));
					$att_li->setAttribute('class',$att->cardinality);
					$atts_list->appendChild($att_li);
				}
				$atts_label->appendChild($atts_list);
			}

			if ($type->description) {
				$desc = $dom->createElement('p');
				$desc->appendChild($dom->createTextNode($type->description));
				$div->appendChild($desc);
			}

			$div->appendChild($xoxo);

			$content->appendChild($div);
			$entry->appendChild($content);

			$xml_link = $dom->createElement('link');
			$xml_link->setAttribute('rel','alternate');
			$xml_link->setAttribute('type','application/xml');
			$xml_link->setAttribute('href',APP_ROOT . "/xml/{$this->ascii_id}/item_type/{$type->ascii_id}");
			$entry->appendChild($xml_link);
			$feed->appendChild($entry);

		}
		$feed_updated->appendChild($dom->createTextNode(date('c',$biggest_timestamp)));
		$dom->appendChild($feed);
		$dom->formatOutput = true;
		return $dom->saveXml();
	}

	function getItemsByAttVal($att_ascii_id,$value_text,$substr = false) {
		$a = new Dase_DB_Attribute;
		$a->ascii_id = $att_ascii_id;
		$a->collection_id = $this->id;
		$a->findOne();

		$v = new Dase_DB_Value;
		$v->attribute_id = $a->id;
		if ($substr) {
			$v->addWhere('value_text',"%$value_text%",'like');
		} else {
			$v->value_text = $value_text;
		}
		$items = array();
		foreach ($v->findAll() as $vrow) {
			$it = new Dase_DB_Item;
			$it->load($vrow['item_id']);
			$items[] = $it;
		}
		return $items;
	}

	function getItemsXmlByAttVal($att_ascii_id,$value_text,$substr = false) {
		$writer = new XMLWriter();
		$writer->openMemory();
		$writer->setIndent(true);
		$writer->startDocument('1.0','UTF-8');
		$writer->startElement('items');
		$writer->writeAttribute('collection',$this->collection_name);
		$writer->writeAttribute($att_ascii_id,$value_text);

		$a = new Dase_DB_Attribute;
		$a->ascii_id = $att_ascii_id;
		$a->collection_id = $this->id;
		$a->findOne();

		$v = new Dase_DB_Value;
		$v->attribute_id = $a->id;
		if ($substr) {
			$v->addWhere('value_text',"%$value_text%",'like');
		} else {
			$v->value_text = $value_text;
		}
		foreach ($v->findAll() as $vrow) {
			$it = new Dase_DB_Item;
			$it->load($vrow['item_id']);
			$writer->startElement('item');
			$writer->writeAttribute('serial_number',$it->serial_number);
			$type = new Dase_DB_ItemType;
			$type->load($vrow['item_id']);
			if (isset($type->name)) {
				$writer->writeAttribute('item_type',$type->name);
			}
			$db = Dase_DB::get();
			$sql = "
				SELECT value_text,ascii_id 
				FROM value, attribute
				WHERE attribute.id = value.attribute_id
				AND value.item_id = $it->id
				";
			$st = $db->query($sql);
			foreach ($st->fetchAll() as $row) {
				$writer->startElement('metadata');
				$writer->writeAttribute('attribute_ascii_id',$row['ascii_id']);
				$writer->text($row['value_text']);
				$writer->endElement();
			}
			$media_file = new Dase_DB_MediaFile;
			$media_file->item_id = $it->id;
			foreach($media_file->findAll() as $mf) {
				$writer->startElement('media_file');
				$writer->writeAttribute('filename',$mf['filename']);
				$writer->writeAttribute('size',$mf['size']);
				$writer->writeAttribute('mime_type',$mf['mime_type']);
				$writer->writeAttribute('width',$mf['width']);
				$writer->writeAttribute('height',$mf['height']);
				$writer->endElement();
			}
			$writer->endElement();
		}
		$writer->endElement();
		$writer->endDocument();
		return $writer->flush(true);
	}

	function getItemsByType($type_ascii_id) {
		$it = new Dase_DB_ItemType;
		$it->collection_id = $this->id;
		$it->ascii_id = $type_ascii_id;
		$it->findOne();
		$ite = new Dase_DB_Item;
		$ite->item_type_id = $it->id;
		foreach ($ite->findAll() as $row) {
			$item = new Dase_DB_ItemType($row);
			$item_array[] = $item;
		}
		return $item_array;
	}

	function getItemsXmlByType($type_ascii_id) {
		$writer = new XMLWriter();
		$writer->openMemory();
		$writer->setIndent(true);
		$writer->startDocument('1.0','UTF-8');
		$writer->startElement('items');
		$writer->writeAttribute('collection',$this->collection_name);

		$type = new Dase_DB_ItemType;
		$type->ascii_id = $type_ascii_id;
		$type->collection_id = $this->id;
		$type->findOne();

		$item = new Dase_DB_Item;
		$item->item_type_id = $type->id;
		$item->status_id = 0;
		foreach($item->findAll() as $row) {

			$it = new Dase_DB_Item($row);
			$writer->startElement('item');
			$writer->writeAttribute('serial_number',$it->serial_number);
			$db = Dase_DB::get();
			$sql = "
				SELECT value_text,ascii_id 
				FROM value, attribute
				WHERE attribute.id = value.attribute_id
				AND value.item_id = $it->id
				";
			$st = $db->query($sql);
			foreach ($st->fetchAll() as $row) {
				$writer->startElement('metadata');
				$writer->writeAttribute('attribute_ascii_id',$row['ascii_id']);
				$writer->text($row['value_text']);
				$writer->endElement();
			}
			$media_file = new Dase_DB_MediaFile;
			$media_file->item_id = $it->id;
			foreach($media_file->findAll() as $mf) {
				$writer->startElement('media_file');
				$writer->writeAttribute('filename',$mf['filename']);
				$writer->writeAttribute('size',$mf['size']);
				$writer->writeAttribute('mime_type',$mf['mime_type']);
				$writer->writeAttribute('width',$mf['width']);
				$writer->writeAttribute('height',$mf['height']);
				$writer->endElement();
			}
			$writer->endElement();
		}
		$writer->endElement();
		$writer->endDocument();
		return $writer->flush(true);
	}

	static function listPublicAsXml() {
		$dom = new DOMDocument('1.0');
		$root = $dom->appendChild($dom->createElement('collections'));
		$c = new Dase_DB_Collection;
		$c->is_public = 1;
		$c->orderBy('collection_name');
		return $c->findAsXml(true);
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

	static function listAsAtom($public_only = 1) {
		$writer = new XMLWriter();
		$writer->openMemory();
		$writer->setIndent(true);
		$writer->startDocument('1.0','UTF-8');
		$writer->startElement('feed');
		$writer->writeAttribute('xmlns','http://www.w3.org/2005/Atom');
		$writer->writeAttribute("xmlns:dase", APP_HTTP_ROOT);
		$writer->writeAttribute('xml:base', APP_HTTP_ROOT);
		$writer->startElement('title');
		$writer->text("DASe Collections");
		$writer->endElement();
		$writer->startElement('id');
		$writer->text(APP_HTTP_ROOT . '/');
		$writer->endElement();
		$writer->startElement('author');
		$writer->startElement('name');
		$writer->endElement();
		$writer->endElement();
		$writer->startElement('updated');
		$writer->text(date('c',Dase_DB_Collection::getLastCreated()));
		$writer->endElement();
		$writer->startElement('link');
		$writer->writeAttribute('rel','self');
		$writer->writeAttribute('type','application/atom+xml');
		$writer->writeAttribute('href',APP_ROOT . "/atom");
		$writer->endElement();
		$c = new Dase_DB_Collection;
		$c->orderBy('collection_name');
		if ($public_only) {
			$c->is_public = true;
			$iter = $c->findAll();
		} else {
			$iter = $c->getAll();
		}
		foreach($iter as $row) {
			$coll = new Dase_DB_Collection($row);
			$writer->startElement('entry');
			$writer->startElement('id');
			$writer->text(APP_ROOT . "/{$coll->ascii_id}/");
			$writer->endElement();
			$writer->startElement('content');
			$writer->text($coll->ascii_id);
			$writer->endElement();
			$writer->startElement('title');
			$writer->text($coll->collection_name);
			$writer->endElement();
			$writer->startElement('updated');
			$writer->text(date('c',$coll->getLastUpdated()));
			$writer->endElement();
			$writer->startElement('summary');
			$writer->text($coll->description);
			$writer->endElement();
			$writer->startElement('link');
			$writer->writeAttribute('rel','alternate');
			$writer->writeAttribute('type','text/html');
			$writer->writeAttribute('href',APP_ROOT . "/{$coll->ascii_id}");
			$writer->endElement();
			$writer->startElement('link');
			$writer->writeAttribute('rel','alternate');
			$writer->writeAttribute('type','application/xml');
			$writer->writeAttribute('href',APP_ROOT . "/xml/{$coll->ascii_id}");
			$writer->endElement();
			if ($coll->is_public) {
				$writer->startElement('category');
				$writer->writeAttribute('scheme',APP_HTTP_ROOT . "/collection_type");
				$writer->writeAttribute('term','public');
				$writer->writeAttribute('label','public');
				$writer->endElement();
			}
			$writer->endElement();
		}
		$writer->endElement();
		$writer->endDocument();
		return $writer->flush(true);
	}

	static function getLookupArray($field) {
		if (in_array($field,array('ascii_id','collection_name'))) {
			$hash = array();
			$c = new Dase_DB_Collection;
			foreach ($c->getAll() as $row) {
				$hash[$row['id']] = $row[$field];
			}
			return $hash;
		}
	}

	function getAttributes($sort = null) {
		$att = new Dase_DB_Attribute;
		$att->collection_id = $this->id;
		if ($sort) {
			$att->orderBy($sort);
		} else {
			$att->orderBy('sort_order');
		}
		$this->attribute_array = $att->findAll();
		return $this->attribute_array;
	}

	function getAdminAttributes() {
		$att = new Dase_DB_Attribute;
		$att->collection_id = 0;
		$att->orderBy('sort_order');
		$admin_attribute_array = $att->findAll();
		$this->admin_attribute_array = $admin_attribute_array;
		return $admin_attribute_array;
	}

	function getAdminAttributeAsciiIds() {
		$att = new Dase_DB_Attribute;
		$att->collection_id = 0;
		$att->orderBy('sort_order');
		$admin_atts = array();
		foreach ($att->findAll() as $row) {
			$admin_atts[$row['id']] = $row['ascii_id'];
		}
		return $admin_atts;
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
		//beware -- this could fill exceed memory limitations!!!
		$db = Dase_DB::get();
		$sql = "
			SELECT *
			FROM item
			where collection_id = $this->id
			";
		$st = $db->query($sql);
		$st->setFetchMode(PDO::FETCH_ASSOC);
		while ($row = $st->fetch()) {
			$items[] = new Dase_DB_Item($row);
		}
		return $items;
	}

	function getItemTypes() {
		$type = new Dase_DB_ItemType;
		$type->collection_id = $this->id;
		$type->orderBy('name');
		$this->item_type_array = $type->findAll();
		return $this->item_type_array;
	}

	public static function insertCollection($xml) {
		$dom = new DOMDocument;
		$dom->loadXML($xml);
		$schema = DASE_PATH . 'schemas/collection.rng';
		if ($dom->relaxNGValidate($schema)) {
			$attribute_nodes = $dom->getElementsByTagname('collection');
			$coll_elem = $attribute_nodes->item(0);
			$coll = new Dase_DB_Collection;
			$coll->ascii_id = $coll_elem->getAttribute('ascii_id');
			if ($coll->find(1)) {
				return $coll->id;
			} 
			$coll->collection_name = $coll_elem->getAttribute('collection_name');
			if (!$coll->collection_name) {
				$coll->collection_name = $coll_elem->nodeValue;
			}
			$coll->path_to_media_files = $coll_elem->getAttribute('path_to_media_files');
			$coll->is_public = $coll_elem->getAttribute('is_public');
			return $coll->insert();
		} else {
			throw new Exception('not a valid collection');
		}
	}

	public static function insertAttributes($ascii_id,$xml) {
		require_once 'Dase/DB/Attribute.php';
		if ($ascii_id) { //allow ascii_id = 0 to be used for admin_atts
			$coll = new Dase_DB_Collection;
			$coll->ascii_id = $ascii_id;
			if (!$coll->findOne()) {
				throw new Exception('no such collection');
			}
		}
		$dom = new DOMDocument;
		$dom->loadXML($xml);
		$schema = DASE_PATH . '/schemas/attributes.rng';
		$count = 0;
		if ($dom->relaxNGValidate($schema)) {
			foreach ($dom->getElementsByTagname('attribute') as $att_node) {
				$att = new Dase_DB_Attribute;
				if (isset($coll->id)) {
					$att->collection_id  = $coll->id;
				}
				$att->ascii_id  = $att_node->getAttribute('ascii_id');
				if (strstr($att->ascii_id,'admin_')) {
					$att->collection_id = 0;
				}
				if (!$att->findOne()) {
					$att->sort_order = $att_node->getAttribute('sort_order');
					$att->attribute_name = $att_node->getAttribute('attribute_name');
					$att->is_public = $att_node->getAttribute('is_public');
					$att->insert();
					$count++;
				} 
			}
		} else {
			//throw new Exception('not a valid attribute set');
			return;
		}
		return $count;
	}	

	public static function insertItem($ascii_id,$xml) {
		require_once 'Dase/DB/Item.php';
		require_once 'Dase/DB/MediaFile.php';
		require_once 'Dase/DB/Value.php';
		$coll = new Dase_DB_Collection;
		$coll->ascii_id = $ascii_id;
		if (!$coll->findOne()) {
			throw new Exception('no such collection');
		}
		$dom = new DOMDocument;
		$dom->loadXML($xml);
		$schema = DASE_PATH . '/schemas/item.rng';
		if ($dom->relaxNGValidate($schema)) {
			$item_nodes = $dom->getElementsByTagname('item');
			$item_elem = $item_nodes->item(0);
			$item = new Dase_DB_Item;
			$item->collection_id  = $coll->id;
			$item->status_id  = 0;
			$item->serial_number  = $item_elem->getAttribute('serial_number');
			$item_id = $item->insert();
			foreach($item_elem->getElementsByTagname('value') as $val_node) {
				$val = new Dase_DB_Value;
				$a = new Dase_DB_Attribute;
				$a->ascii_id = $val_node->getAttribute('attribute_ascii_id');
				if (strstr($a->ascii_id,'admin_')) {
					$a->collection_id = 0;
				} else {
					$a->collection_id = $coll->id;
				}
				$a->findOne();
				$val->attribute_id = $a->id;
				$val->value_text = $val_node->nodeValue;
				$val->item_id = $item_id;
				$val->insert();
			}
			foreach($item_elem->getElementsByTagname('media_file') as $mf_node) {
				$mf = new Dase_DB_MediaFile;
				$mf->filename = $mf_node->getAttribute('filename');
				$mf->size = $mf_node->getAttribute('size');
				$mf->mime_type = $mf_node->getAttribute('mime_type');
				$mf->width = $mf_node->getAttribute('width');
				$mf->height = $mf_node->getAttribute('height');
				$mf->item_id = $item_id;
				$mf->insert();
			}
		} else {
			//throw new Exception('not a valid item');
			return;
		}
		return "inserted item id $item_id";
	}	

	public function buildSearchIndex() {
		$db = Dase_DB::get();
		$db->query("DELETE FROM search_table WHERE collection_id = $this->id");
		$db->query("DELETE FROM admin_search_table WHERE collection_id = $this->id");
		$item = new Dase_DB_Item;
		$item->collection_id = $this->id;
		foreach ($item->findAll() as $it) {
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
			$st->execute(array($it['id']));
			while ($value_text = $st->fetchColumn()) {
				$composite_value_text .= $value_text . " ";
			}
			$search_table = new Dase_DB_SearchTable;
			$search_table->value_text = $composite_value_text;
			$search_table->item_id = $it['id'];
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
			$st->execute(array($it['id']));
			while ($value_text = $st->fetchColumn()) {
				$composite_value_text .= $value_text . " ";
			}
			$search_table = new Dase_DB_AdminSearchTable;
			$search_table->value_text = $composite_value_text;
			$search_table->item_id = $it['id'];
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

	function getLastUpdated() {
		$item = new Dase_DB_Item;
		$item->collection_id = $this->id;
		$item->orderBy('last_update DESC');
		$item->setLimit(1);
		$item->findOne();
		//return date('c',$item->last_update);
		return $item->last_update;
	}
}
