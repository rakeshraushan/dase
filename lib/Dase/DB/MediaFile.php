<?php

require_once 'Dase/DB/Autogen/MediaFile.php';

class Dase_DB_MediaFile extends Dase_DB_Autogen_MediaFile 
{
	public $url = '';

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
		foreach($this->query($sql,$params) as $row) {
			$new = $csx->addChild('media_file');
			foreach($row as $k => $v) {
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
		return $csx;
	}
}
