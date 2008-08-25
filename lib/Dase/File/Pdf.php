<?php

class Dase_File_Pdf extends Dase_File
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

	public function addToCollection($item,$check_for_dups) {}

	function makeThumbnail($item)
	{
		$collection = $item->getCollection();
		if (!file_exists(Dase_Config::get('path_to_media').'/'.$collection->ascii_id . "/thumbnail/pdf.jpg")) {
			copy(DASE_PATH . '/images/thumb_icons/pdf.jpg',Dase_Config::get('path_to_media').'/'.$collection->ascii_id . '/thumbnail/pdf.jpg');
		}
		$media_file = new Dase_DBO_MediaFile;
		$media_file->item_id = $item->id;
		$media_file->filename = 'pdf.jpg';
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
		if (!file_exists(Dase_Config::get('path_to_media').'/'.$collection->ascii_id . "/viewitem/pdf.jpg")) {
			copy(DASE_PATH . '/images/thumb_icons/pdf.jpg',Dase_Config::get('path_to_media').'/'.$collection->ascii_id . '/viewitem/pdf.jpg');
		}
		$media_file = new Dase_DBO_MediaFile;
		$media_file->item_id = $item->id;
		$media_file->filename = 'pdf.jpg';
		$media_file->width = 80;
		$media_file->height = 80;
		$media_file->mime_type = 'image/jpeg';
		$media_file->size = 'viewitem';
		$media_file->p_collection_ascii_id = $collection->ascii_id;
		$media_file->p_serial_number = $item->serial_number;
		$media_file->insert();
		Dase_Log::info("created $media_file->size $media_file->filename");
	}

	function processFile($item)
	{
		$collection->getItem();
		$dest = Dase_Config::get('path_to_media').'/'.$collection->ascii_id . "/pdf/" . $item->serial_number . '.pdf';
		$this->copyTo($dest);
		$media_file = new Dase_DBO_MediaFile;

		foreach ($this->getMetadata() as $term => $value) {
			$media_file->addMetadata($term,$value);
		}

		$media_file->item_id = $item->id;
		$media_file->filename = $item->serial_number . '.pdf';
		$media_file->file_size = $this->file_size;
		$media_file->mime_type = $this->mime_type;
		$media_file->size = 'pdf';
		$media_file->p_collection_ascii_id = $collection->ascii_id;
		$media_file->p_serial_number = $item->serial_number;
		$media_file->insert();
		Dase_Log::info("created $media_file->size $media_file->filename");
	}

}

