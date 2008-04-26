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

	public function getLocalPath()
	{
		$c = $this->getCollection();
		$size = $this->size;
		if ('viewitem' == $size) {
			$size = '400';
		}
		if ('thumbnail' == $size) {
			$size = 'thumbnails';
		}
		return $c->path_to_media_files . '/' . $size . '/' . $this->filename; 
	}

	public function resize($geometry)
	{
		$file = $this->getLocalPath();
		if (file_exists($file)) {
			$results = exec("/usr/bin/convert \"$file\" -format jpeg -resize '$geometry >' -colorspace RGB $file");
			$file_info = getimagesize($file);
			$this->width = $file_info[0];
			$this->height = $file_info[1];
			$this->update();
		}
	}

	function getMd5() {
		$file = $this->getLocalPath();
		if (file_exists($file)) {
			return md5_file($file);
		} else {
			return false;
		}
	}

	function getFileSize() {
		$file = $this->getLocalPath();
		if (file_exists($file)) {
			return filesize($file);
		} else {
			return false;
		}
	}

	function getLink() {
		return APP_ROOT . "/media/{$this->p_collection_ascii_id}/$this->size/$this->filename";
	}

	function getRelativeLink() {
		return "media/{$this->p_collection_ascii_id}/$this->size/$this->filename";
	}

	function asAtom() 
	{
		$entry = new Dase_Atom_Entry;
		//may need to add edit links here
		return $this->injectAtomEntryData($entry);
	}

	function deleteValues() 
	{
		$db = Dase_DB::get();
		$sql = "
			DELETE
			FROM media_value 
			WHERE media_file_id = $this->id
			";
		$db->query($sql);
	}

	function expunge() 
	{
		$this->deleteValues();
		$this->delete();
	}

	function addMetadata($term,$text,$overwrite=true) 
	{
		$att = new Dase_DBO_MediaAttribute;
		$att->term = $term;
		if ($att->findOne()) {
			$val = new Dase_DBO_MediaValue;
			$val->media_attribute_id = $att->id;
			$val->media_file_id = $this->id;
			if ($val->findOne()) {
				if ($overwrite) {
					$val->text = $text;
					$val->update();
				}
			} else {
				$val->text = $text;
				$val->insert();
			}
		}
	}

	function injectAtomEntryData(Dase_Atom_Entry $entry)
	{
		//this function assumes p_collection_ascii_id & p_serial_number are set
		$item = $this->getItem();
		$entry->setId($this->getLink());
		$entry->setTitle($item->getTitle());
		$entry->addAuthor();

		//todo: add 'updated' column to media_file table
		$entry->setUpdated($this->updated);
		$entry->setSummary('');

		//todo: atompub edit & edit-media links
		$edit_media_url = APP_ROOT .'/edit-media/'.$this->p_collection_ascii_id.'/'.$this->p_serial_number.'/media/'.$this->size;
		$entry->addLink($edit_media_url,'edit-media');
		$edit_url = APP_ROOT .'/edit/'.$this->p_collection_ascii_id.'/'.$this->p_serial_number.'/media/'.$this->size;
		$entry->addLink($edit_url,'edit');
		$entry->setMediaContent($this->getLink(),$this->mime_type);
		return $entry->asXml();
	}
}
