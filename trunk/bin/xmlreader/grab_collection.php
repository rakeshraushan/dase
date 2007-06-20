<?php
ini_set('include_path',ini_get('include_path').':./../lib:'); 
require_once 'Dase/Timer.php';
require_once 'Dase/DB/Collection.php';
require_once 'Dase/Collection.php';

Dase_Timer::start();

$reg = Dase_Registry::getInstance();
$reg->setConf('../inc/config.php');


$xml = new SimpleXMLElement(Dase_Collection::getAll('laits'));
foreach ($xml->collection as $collection) {
	//asXML is a SimpleXML function
	print "id: " . Dase_Collection::insertCollection($collection->asXML()) . "\n";
}

print Dase_Timer::getElapsed();


function getCollectionXml($xml) {
	$atts = array();
	$reader = new XMLReader();
	//$reader->open("$xml");
	$reader->XML($xml);
	$coll = new Dase_DB_Collection;
	while ($reader->read()) {
		if ($reader->nodeType == XMLREADER::ELEMENT && $reader->localName == 'collection') {
			$reader->moveToAttribute('ascii_id');
			$coll->ascii_id = $reader->value; 
			list($check) = $coll->find();
			if ($check) {
				echo "found " . $check->ascii_id . "\n";
				return $check->getId();
			}
			$reader->moveToAttribute('collection_name');
			$coll->collection_name = $reader->value; 
			$reader->moveToAttribute('path_to_media_files');
			$coll->path_to_media_files = $reader->value; 
			$reader->moveToAttribute('is_public');
			$coll->is_public = $reader->value; 
			$coll->insert();
			echo "inserted " . $coll->collection_name . "\n";
		}
		if ($reader->nodeType == XMLREADER::ELEMENT && $reader->localName == 'attribute') {
			$att = new DB_Dase_Attribute;
			$reader->moveToAttribute('ascii_id');
			$att->ascii_id = $reader->value; 
			if ('admin_' == substr($att->ascii_id, 0, 6)) {
				$att->collection_id = 0;
			} else {
				$att->collection_id = $coll->getId();
			}
			if (!count($att->find())) {
				$reader->moveToAttribute('attribute_name');
				$att->attribute_name = $reader->value; 
				$reader->moveToAttribute('is_public');
				$att->is_public = $reader->value; 
				$att->insert();
				echo "inserted " . $coll->collection_name . " attribute\n";
			}
		}
		if ($reader->nodeType == XMLREADER::ELEMENT && $reader->localName == 'category') {
			//work on cross-collection categories!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
			$cat = new DB_Dase_Category;
			$reader->moveToAttribute('ascii_id');
			$cat->ascii_id = $reader->value; 
			$cat->collection_id = $coll->getId();
			if (!count($cat->find())) {
				$reader->moveToAttribute('name');
				$cat->name = $reader->value; 
				$reader->moveToAttribute('is_public');
				$cat->is_public = $reader->value; 
				$cat->insert();
				echo "inserted " . $coll->collection_name . " category\n";
			}
		}
		if ($reader->nodeType == XMLREADER::ELEMENT && $reader->localName == 'manager') {
			$man = new DB_Dase_CollectionManager;
			$man->collection_ascii_id = $coll->ascii_id;
			$reader->moveToAttribute('dase_user_eid');
			$man->dase_user_eid = $reader->value; 
			$reader->moveToAttribute('auth_level');
			$man->auth_level = $reader->value; 
			if (!count($man->find())) {
				$man->insert();
				echo "inserted " . $coll->collection_name . " manager\n";
			}
		}
	}
	return $coll->getId();
}

function getItemXml($items_xml_dir,$coll_id) {
	foreach (new DirectoryIterator( $items_xml_dir ) as $file) {
		if (! $file->isDot()) {
			$metadata = array();
			$reader = new XMLReader();
			$reader->open("$items_xml_dir/$file");
			while ($reader->read()) {
				if ($reader->nodeType == XMLREADER::ELEMENT && $reader->localName == 'item') {
					$reader->moveToAttribute('serial_number');
					$ser_num = $reader->value; 
					$item = new DB_Dase_Item;
					$item->serial_number = $ser_num;
					$item->collection_id = $coll_id;
					$item_id = $item->insert();
				}
				if ($reader->nodeType == XMLREADER::ELEMENT && $reader->localName == 'metadata') {
					$att = new DB_Dase_Attribute;
					$reader->moveToAttribute('attribute_ascii_id');
					$att_ascii_id = $reader->value; 
					if ('admin_' == substr($att_ascii_id, 0, 6)) {
						$att->collection_id = 0;
					} else {
						$att->collection_id = $coll_id;
					}
					$att->ascii_id = $att_ascii_id;
					list($found_att) = $att->find();
					if ($found_att) {
						$att_id = $found_att->getId();
					} else {
						$reader->moveToAttribute('attribute_name');
						$att->attribute_name = $reader->value; 
						$reader->moveToAttribute('is_public');
						$att->is_public = $reader->value; 
						$att_id = $att->insert();
					}
					$reader->read();
					$value_text = $reader->value;
					$value = new DB_Dase_Value;
					$value->attribute_id = $att_id;
					$value->item_id = $item_id;
					$value->value_text = $value_text;
					$value->insert();
				}
				if ($reader->nodeType == XMLREADER::ELEMENT && $reader->localName == 'media_file') {
					$media_file = new DB_Dase_MediaFile;
					$reader->moveToAttribute('height');
					$media_file->height = $reader->value; 
					$reader->moveToAttribute('width');
					$media_file->width = $reader->value; 
					$reader->moveToAttribute('size');
					$media_file->size = $reader->value; 
					$reader->moveToAttribute('mime_type');
					$media_file->mime_type = $reader->value; 
					$media_file->item_id = $item_id;
					$media_file->insert();
				}
			}
		}
	}
}
