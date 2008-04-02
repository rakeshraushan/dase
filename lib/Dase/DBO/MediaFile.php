<?php

require_once 'Dase/DBO/Autogen/MediaFile.php';

class Dase_DBO_MediaFile extends Dase_DBO_Autogen_MediaFile 
{
	public $url = '';

	function getItem()
	{
		$item = new Dase_DBO_Item;
		$item->load($this->item_id);
		return $item;
	}

	function getCollection()
	{
		$coll = new Dase_DBO_Collection;
		$coll->load($this->getItem()->collection_id);
		return $coll;
	}

	public function resize($geometry)
	{
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

	function getLink() {
		return APP_ROOT . "/media/{$this->p_collection_ascii_id}/$this->size/$this->filename";
	}

	function asAtom() 
	{
		//this function assumes p_collection_ascii_id & p_serial_number are set
		$item = $this->getItem();
		$entry = new Dase_Atom_Entry;
		$entry->setId($this->getLink());
		$entry->setTitle($item->getTitle());
		$entry->addAuthor();
		//todo: add updated column to media_file table
		$entry->setUpdated(date(DATE_ATOM));
		$entry->setSummary('');
		//todo: atompub edit & edit-media links
		$entry->setMediaContent($this->getLink(),$this->mime_type);
		return $entry->asXml();
	}
}
