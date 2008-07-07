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

	function setMetadata($term,$text)
	{
		$media_att = Dase_DBO_MediaAttribute::findOrCreate($term);
		$media_val = new Dase_DBO_MediaValue;
		$media_val->media_file_id = $this->id;
		$media_val->media_attribute_id = $media_att->id;
		$media_val->text = $text;
		return $media_val->insert();
	}

	function deleteMetadata() 
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
		$this->deleteMetadata();
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

	public function getMetadata($term = '')
	{
		$metadata = array();
		$bound_params = array();
		$db = Dase_DB::get();
		$sql = "
			SELECT a.term, a.label,v.text,v.id
			FROM media_attribute a, media_value v
			WHERE v.media_file_id = ?
			AND v.media_attribute_id = a.id
			ORDER BY a.sort_order,v.text
			";
		$bound_params[] = $this->id;
		if ($term) {
			$sql .= "
				AND a.term = ?
				";
			$bound_params[] = $att_ascii_id;
		}
		$st = $db->prepare($sql);
		$st->execute($bound_params);
		while ($row = $st->fetch()) {
			$metadata[] = $row;
		}
		return $metadata;
	}

	function injectAtomEntryData(Dase_Atom_Entry $entry)
	{
		$d = "http://daseproject.org/ns/1.0";
		//this function assumes p_collection_ascii_id & p_serial_number are set
		$item = $this->getItem();
		$entry->setId($this->getLink());
		$entry->setTitle($item->getTitle());
		$entry->addAuthor();

		//todo: add 'updated' column to media_file table
		$entry->setUpdated($this->updated);
		$entry->setSummary('');

		foreach ($this->getMetadata() as $row) {
			//php dom will escape text for me here....
			$meta = $entry->addElement('d:'.$row['term'],$row['text'],$d);
			$meta->setAttribute('d:label',$row['label']);
		}

		//todo: atompub edit & edit-media links
		$edit_media_url = APP_ROOT .'/edit-media/'.$this->p_collection_ascii_id.'/'.$this->p_serial_number.'/media/'.$this->size;
		$entry->addLink($edit_media_url,'edit-media');
		$edit_url = APP_ROOT .'/edit/'.$this->p_collection_ascii_id.'/'.$this->p_serial_number.'/media/'.$this->size;
		$entry->addLink($edit_url,'edit');
		$entry->setMediaContent($this->getLink(),$this->mime_type);
		return $entry->asXml();
	}
}
