<?php

class Dase_Upload
{
	//this class represents an instance of a file being added to an item

	protected $file;
	protected $item;
	protected $collection;
	private $is_dup = false;
	protected $metadata = array();

	public function __construct(Dase_File $file,$collection_ascii_id,$overwrite = false) {
		$this->file = $file;
		$this->collection = Dase_DB_Collection::get($collection_ascii_id);
		if (!$this->isDuplicate()) {
			if ($overwrite) {
				//in theory, this'll get the existing item w/ proper sernum
				//this should also be a safe no-side-effect way to check for dups
				//note thaht the use case is after image color-correction, so it will NOT be a dup
				$this->item = Dase_DB_Item::retrieve($collection_ascii_id,$this->file->getFilename());
			} else {
				$this->item = Dase_DB_Item::create($collection_ascii_id);
			}
		}
	}

	function isDuplicate() {
		$meta = $this->file->getMetadata();
		$v = new Dase_DB_Value;
		$v->attribute_id = Dase_DB_Attribute::getAdmin('admin_checksum')->id;
		$v->value_text = $meta['admin_checksum'];
		foreach ($v->findAll() as $row) {
			$it = new Dase_DB_Item;
			$it->load($row['item_id']);
			if ($it->collection_id == $this->collection->id) {
				print "duplicate file found {$meta['admin_filepath']}\n";
				$this->is_dup = true;
				return true;
			}
		}
		return false;
	}

	function ingest() {
		if (!$this->is_dup) {
			$this->file->makeThumbnail($this->item,$this->collection);
			$this->file->makeViewitem($this->item,$this->collection);
			$this->file->makeSizes($this->item,$this->collection);

			foreach ($this->getMetadata() as $ascii => $val) {
				$this->item->setValue($ascii,$val);
			}
		}
	}

	function setTitle() {
		if (!$this->is_dup) {
			$name = $this->file->getFilename();
			$this->item->setValue('title',$name);
		}
	}

	function getMetadata() {
		$this->metadata = $this->file->getMetadata();
		//$this->metadata['admin_corrected_image_upload_date'] = 
		//$this->metadata['admin_project_name'] = 
		//$this->metadata['admin_upload_eid'] = 
		//$this->metadata['admin_upload_ip_address'] = 
		$this->metadata['admin_serial_number'] = $this->item->serial_number; 
		$this->metadata['admin_upload_date_time'] = time(); //NOTE this needs to be admin_upload_timestamp
		return $this->metadata;
	}	
}
