<?php

class Dase_File_Doc extends Dase_File
{
	protected $metadata = array();

	function __construct($file,$mime='')
	{
		parent::__construct($file,$mime);
	}

	function getMetadata()
	{
		$this->metadata = parent::getMetadata();
		return $this->metadata;
	}

	public function addToCollection($item,$check_for_dups) 
	{
		$media_file = parent::addToCollection($item,$check_for_dups);
		$this->makeThumbnail($item);
		$this->makeViewitem($item);
		return $media_file;
	}

	function makeThumbnail($item)
	{
		$collection = $item->getCollection();
		if (!file_exists(Dase_Config::get('path_to_media').'/'.$collection->ascii_id . "/thumbnail/doc.jpg")) {
			copy(DASE_PATH . '/www/images/thumb_icons/doc.jpg',Dase_Config::get('path_to_media').'/'.$collection->ascii_id . '/thumbnail/doc.jpg');
		}
		$media_file = new Dase_DBO_MediaFile;
		$media_file->item_id = $item->id;
		$media_file->filename = 'doc.jpg';
		$media_file->width = 80;
		$media_file->height = 80;
		$media_file->mime_type = 'image/jpeg';
		$media_file->size = 'thumbnail';
		$media_file->p_collection_ascii_id = $collection->ascii_id;
		$media_file->p_serial_number = $item->serial_number;
		$media_file->insert();
		Dase_Log::info("created $media_file->size $media_file->filename");
	}

	function makeViewitem($item)
	{
		$collection = $item->getCollection();
		if (!file_exists(Dase_Config::get('path_to_media').'/'.$collection->ascii_id . "/viewitem/doc.jpg")) {
			copy(DASE_PATH . '/www/images/thumb_icons/doc.jpg',Dase_Config::get('path_to_media').'/'.$collection->ascii_id . '/viewitem/doc.jpg');
		}
		$media_file = new Dase_DBO_MediaFile;
		$media_file->item_id = $item->id;
		$media_file->filename = 'doc.jpg';
		$media_file->width = 80;
		$media_file->height = 80;
		$media_file->mime_type = 'image/jpeg';
		$media_file->size = 'viewitem';
		$media_file->p_collection_ascii_id = $collection->ascii_id;
		$media_file->p_serial_number = $item->serial_number;
		$media_file->insert();
		Dase_Log::info("created $media_file->size $media_file->filename");
	}
}

