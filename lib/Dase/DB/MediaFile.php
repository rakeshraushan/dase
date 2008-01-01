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
