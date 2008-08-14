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

	public function addToCollection($title,$uid,$collection,$check_for_dups) {}

	function makeThumbnail($item,$collection)
	{
		if (!file_exists(Dase_Config::get('path_to_media').'/'.$collection->ascii_id . "/thumbnails/doc.jpg")) {
			copy(DASE_PATH . '/images/thumb_icons/doc.jpg',Dase_Config::get('path_to_media').'/'.$collection->ascii_id . '/thumbnails/doc.jpg');
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

	function makeViewitem($item,$collection)
	{
		if (!file_exists(Dase_Config::get('path_to_media').'/'.$collection->ascii_id . "/400/doc.jpg")) {
			copy(DASE_PATH . '/images/thumb_icons/doc.jpg',Dase_Config::get('path_to_media').'/'.$collection->ascii_id . '/400/doc.jpg');
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

	function processFile($item,$collection)
	{
		//todo: insert media metadata
		$dest = Dase_Config::get('path_to_media').'/'.$collection->ascii_id . "/doc/" . $item->serial_number . '.doc';
		$this->copyTo($dest);
		$media_file = new Dase_DBO_MediaFile;

		foreach ($this->getMetadata() as $term => $value) {
			$media_file->addMetadata($term,$value);
		}

		$media_file->item_id = $item->id;
		$media_file->filename = $item->serial_number . '.doc';
		$media_file->file_size = $this->file_size;
		$media_file->mime_type = $this->mime_type;
		$media_file->size = 'doc';
		$media_file->p_collection_ascii_id = $collection->ascii_id;
		$media_file->p_serial_number = $item->serial_number;
		$media_file->insert();
		Dase_Log::info("created $media_file->size $media_file->filename");
	}

}

