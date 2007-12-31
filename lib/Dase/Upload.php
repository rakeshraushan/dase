<?php

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

	public function __construct(Dase_File $file,Dase_DB_Collection $collection,$check_for_dup = true) {
		$this->file = $file;
		$this->collection = $collection;
		if ($check_for_dup && $this->isDuplicate()) {
			throw new Dase_Upload_Exception("Error: duplicate file found: " . $this->file->getFilepath());
		}
	}

	function createItem() {
		$this->item = Dase_DB_Item::create($this->collection->ascii_id);
		return $this->item->serial_number;
	}

	function getItem() {
		return $this->item;
	}

	function retrieveItem() {
		$this->item = Dase_Item::get($this->collection->ascii_id,$this->file->getFilename());
		if ($this->item->id) {
			return "RETRIEVED " . $this->item->serial_number . "\n";
		} else {
			return "NO ITEM RETRIEVED (" . $this->file->getFilename() . ")\n";
		}	
	}

	function checkForMultiTiff() {
		$image = new Imagick($this->file->getFilepath());
		if (1 < $image->getNumberImages()) {
			throw new Dase_Upload_Exception("Error: " . $this->file->getFilepath() . " appears to be a multi-layered tiff\n");
		} else {
			return 0;
		}
	}

	function isDuplicate() {
		$meta = $this->file->getMetadata();
		$v = new Dase_DB_Value;
		$v->attribute_id = Dase_DB_Attribute::getAdmin('admin_checksum')->id;
		$v->value_text = $meta['admin_checksum'];
		foreach ($v->find() as $val) {
			$it = new Dase_DB_Item;
			$it->load($val->item_id);
			if ($it->collection_id == $this->collection->id) {
				return true;
			}
		}
		return false;
	}

	function deleteItemMedia() {
		$msg = '';
		$mf = new Dase_DB_MediaFile;
		$mf->item_id = $this->item->id;
		foreach ($mf->find() as $doomed) {
			$msg .= "DELETING $doomed->size for " . $this->item->serial_number . "\n";
			$doomed->delete();
		}
		return $msg;
	}

	function moveFileTo($destdir) {
		$dest = rtrim($destdir,'/') . '/' . $this->file->getBasename(); 
		try {
			$this->file->moveTo($dest);
		} catch (Exception $e){
			throw new Dase_Upload_Exception("Error: could not move " . $this->file->getFilepath() . " to $dest\n");
		}
		return "MOVED " . $this->file->getFilepath() . " to $dest\n";
	}

	function ingest() {
		$msg = '';
		$msg .= $this->file->makeThumbnail($this->item,$this->collection);
		$msg .= $this->file->makeViewitem($this->item,$this->collection);
		$msg .= $this->file->makeSizes($this->item,$this->collection);

		foreach ($this->getMetadata() as $ascii => $val) {
			$this->item->setValue($ascii,$val);
		}
		$msg .= "added admin metadata\n";
		return $msg;
	}

	function buildSearchIndex() {
		return $this->item->buildSearchIndex();
	}

	function deleteItemAdminMetadata() {
		return $this->item->deleteAdminValues();
	}

	function setTitle() {
		$name = $this->file->getFilename();
		$this->item->setValue('title',$name);
	}

	function setMetadata($att_ascii_id,$value) {
		//need to check here is att_ascii_id is valid!!!!
		//now it fails silently
		$this->item->setValue($att_ascii_id,$value);
	}

	function getMetadata() {
		$this->metadata = $this->file->getMetadata();
		//$this->metadata['admin_corrected_image_upload_date'] = 
		//$this->metadata['admin_project_name'] = 
		//$this->metadata['admin_upload_eid'] = 
		//$this->metadata['admin_upload_ip_address'] = 
		$this->metadata['admin_serial_number'] = $this->item->serial_number; 
		$this->metadata['admin_upload_date_time'] = date(DATE_ATOM); //NOTE this needs to be admin_upload_timestamp
		return $this->metadata;
	}	
}
