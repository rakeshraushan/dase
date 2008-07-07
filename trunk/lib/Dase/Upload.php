<?php
/*
 * Copyright 2008 The University of Texas at Austin
 *
 * This file is part of DASe.
 * 
 * DASe is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * DASe is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with DASe.  If not, see <http://www.gnu.org/licenses/>.
 */ 

class Dase_Upload_Exception extends Exception {
}

class Dase_Upload
{
	//this class represents an instance of a file being added to an item

	private $exception = null;
	protected $collection;
	protected $file;
	protected $item;
	protected $metadata = array();
	public $message = '';

	public function __construct(Dase_File $file,Dase_DBO_Collection $collection,$check_for_dup = true)
	{
		$this->file = $file;
		$this->collection = $collection;
		if ($check_for_dup && $this->isDuplicate()) {
			throw new Dase_Upload_Exception("Error: duplicate file found: " . $this->file->getFilepath());
		}
	}

	function createItem($eid=null)
	{
		$this->item = Dase_DBO_Item::create($this->collection->ascii_id,null,$eid);
		return $this->item->serial_number;
	}

	function setItem($item)
	{
		$this->item = $item;
	}

	function getItem()
	{
		return $this->item;
	}

	function getDaseFileSize() {
		return $this->file->size;
	}

	function retrieveItem()
	{
		$this->item = Dase_DBO_Item::get($this->collection->ascii_id,$this->file->getFilename());
		if ($this->item->id) {
			return "RETRIEVED " . $this->item->serial_number . "\n";
		} else {
			return "NO ITEM RETRIEVED (" . $this->file->getFilename() . ")\n";
		}	
	}

	function checkForMultiTiff()
	{
		$image = new Imagick($this->file->getFilepath());
		if (1 < $image->getNumberImages()) {
			throw new Dase_Upload_Exception("Error: " . $this->file->getFilepath() . " appears to be a multi-layered tiff\n");
		} else {
			return 0;
		}
	}

	function isDuplicate()
	{
		//todo: use file metadata
		$meta = $this->file->getMetadata();
		$v = new Dase_DBO_Value;
		$v->attribute_id = Dase_DBO_Attribute::getAdmin('admin_checksum')->id;
		$v->value_text = $meta['admin_checksum'];
		foreach ($v->find() as $val) {
			$it = new Dase_DBO_Item;
			$it->load($val->item_id);
			if ($it->collection_id == $this->collection->id) {
				return true;
			}
		}
		return false;
	}

	/** used when swapping in new media */
	function deleteItemMedia()
	{
		$msg = '';
		$mf = new Dase_DBO_MediaFile;
		$mf->item_id = $this->item->id;
		foreach ($mf->find() as $doomed) {
			$msg .= "DELETING $doomed->size for " . $this->item->serial_number . "\n";
			$doomed->delete();
		}
		return $msg;
	}

	function moveFileTo($destdir)
	{
		$dest = rtrim($destdir,'/') . '/' . $this->file->getBasename(); 
		try {
			$this->file->moveTo($dest);
		} catch (Exception $e){
			throw new Dase_Upload_Exception("Error: could not move " . $this->file->getFilepath() . " to $dest\n");
		}
		return "MOVED " . $this->file->getFilepath() . " to $dest\n";
	}

	function ingest()
	{
		Dase_Log::info('attempting to ingest file');
		$msg = '';
		$msg .= $this->file->makeThumbnail($this->item,$this->collection);
		$msg .= $this->file->makeViewitem($this->item,$this->collection);
		$msg .= $this->file->processFile($this->item,$this->collection);

		foreach ($this->getMetadata() as $ascii => $val) {
			$this->item->setValue($ascii,$val);
		}
		$msg .= "added admin metadata\n";
		return $msg;
	}

	function buildSearchIndex()
	{
		return $this->item->buildSearchIndex();
	}

	function deleteItemAdminMetadata()
	{
		return $this->item->deleteAdminValues();
	}

	function setTitle($title='')
	{
		if (!$title) {
			$title = $this->file->getFilename();
		}
		$this->item->setValue('title',$title);
	}

	function setMetadata($att_ascii_id,$value)
	{
		//need to check here is att_ascii_id is valid!!!!
		//now it fails silently
		$this->item->setValue($att_ascii_id,$value);
	}

	function getMetadata()
	{
		$this->metadata = $this->file->getMetadata();
		//$this->metadata['admin_corrected_image_upload_date'] = 
		//$this->metadata['admin_upload_eid'] = 
		//$this->metadata['admin_upload_ip_address'] = 
		$this->metadata['admin_serial_number'] = $this->item->serial_number; 
		$this->metadata['admin_upload_date_time'] = date(DATE_ATOM); //NOTE this needs to be admin_upload_timestamp
		return $this->metadata;
	}	

	public function getFilename()
	{
		return $this->file->getFilename();
	}
	public function	getFileSize()
	{
		return $this->file->getFileSize();
	}
	public function	getFiletype()
	{
		return $this->file->getFiletype();
	}
	public function	getTitle()
	{
		return $this->item->getTitle();
	}
	public function	getItemUrl()
	{
		return $this->item->getBaseUrl();
	}
	public function	getThumbnailUrl()
	{
		return $this->item->getMediaUrl('thumbnail');
	}
}
