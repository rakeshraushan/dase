<?php

require_once 'Dase/DB/Autogen/MediaFile.php';

class Dase_DB_MediaFile extends Dase_DB_Autogen_MediaFile 
{
	public $url = '';

	function getCollection() {
		$item = new Dase_DB_Item;
		$item->load($this->item_id);
		$coll = new Dase_DB_Collection;
		$coll->load($item->collection_id);
		return $coll;
	}

	function resultSetAsSimpleXml() {
		if (!$this->item_id) {
			throw new Exception('must specify item_id'); 
		}
		$sql = "
			SELECT * 
			FROM media_file
			WHERE item_id = ? 
			";	
		$params[] = $this->item_id;
		$csx = new SimpleXMLElement("<media_files/>");
		$sizes = array();
		foreach($this->query($sql,$params) as $row) {
			$new = $csx->addChild('media_file');
			foreach($row as $k => $v) {
				if ('size' == $k) {
					$sizes[] = $v;
				}
				if ($v) {
					$new->addAttribute($k,$v);
				}
			}
			if (!$row['p_collection_ascii_id']) {
				//should simply make sure that all media files
				//have proper p_coll_ascii_id rather than do this
				$c = new Dase_DB_Collection;
				$item = new Dase_DB_Item;
				$item->load($row['item_id']);
				$c->load($item->collection_id);
				$row['p_collection_ascii_id'] = $c->ascii_id;
			}
			$new->addAttribute('url',APP_ROOT . '/media/' . $row['p_collection_ascii_id'] . '/' . $row['size'] . '/' . $row['filename']);
			$node1 = dom_import_simplexml($new);
			$node1->appendChild(new DOMText($row['filename']));
		}
		//this is to guarantee there is a thumbnail and viewitem
		foreach(array('thumbnail','viewitem') as $size) {
			if (!in_array($size,$sizes)) {
				$new = $csx->addChild('media_file');
				$new->addAttribute('item_id',$this->item_id);
				$new->addAttribute('width',80);
				$new->addAttribute('height',80);
				$new->addAttribute('mime_type','image/jpeg');
				$new->addAttribute('size',$size);
				$new->addAttribute('url',APP_ROOT . '/images/unavail.jpg');
				$node1 = dom_import_simplexml($new);
				$node1->appendChild(new DOMText('image unavailable'));
			}
		}
		return $csx;
	}

	public function resize($geometry) {
		$c = $this->getCollection();
		$file = $c->path_to_media_files . '/' . $this->size . '/' . $this->filename; 
		if (file_exists($file)) {
			$results = exec("/usr/bin/convert \"$file\" -format jpeg -resize '$geometry >' -colorspace RGB $file");
			$file_info = getimagesize($file);
			$this->width = $file_info[0];
			$this->height = $file_info[1];
			$this->update();
		}
	}
}
