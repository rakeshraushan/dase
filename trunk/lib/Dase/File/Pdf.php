<?php

class Dase_File_Pdf extends Dase_File
{
	protected $metadata = array();

	function __construct($file) {
		parent::__construct($file);
	}

	function getMetadata() {
		$this->metadata = parent::getMetadata();
		return $this->metadata;
	}

	function makeThumbnail($item,$collection) {
		if (!file_exists($collection->path_to_media_files . "/thumbnails/pdf.jpg")) {
			copy(DASE_PATH . '/images/thumb_icons/pdf.jpg',$collection->path_to_media_files . '/thumbnails/pdf.jpg');
		}
		$media_file = new Dase_DB_MediaFile;
		$media_file->item_id = $item->id;
		$media_file->filename = 'pdf.jpg';
		$media_file->width = 80;
		$media_file->height = 80;
		$media_file->mime_type = 'image/jpeg';
		$media_file->size = 'thumbnail';
		$media_file->p_collection_ascii_id = $collection->ascii_id;
		$media_file->p_serial_number = $item->serial_number;
		$media_file->insert();
		return "created $media_file->filename\n";
	}

	function makeViewitem($item,$collection) {
		if (!file_exists($collection->path_to_media_files . "/400/pdf.jpg")) {
			copy(DASE_PATH . '/images/thumb_icons/pdf.jpg',$collection->path_to_media_files . '/400/pdf.jpg');
		}
		$media_file = new Dase_DB_MediaFile;
		$media_file->item_id = $item->id;
		$media_file->filename = 'pdf.jpg';
		$media_file->width = 80;
		$media_file->height = 80;
		$media_file->mime_type = 'image/jpeg';
		$media_file->size = 'viewitem';
		$media_file->p_collection_ascii_id = $collection->ascii_id;
		$media_file->p_serial_number = $item->serial_number;
		$media_file->insert();
		return "created $media_file->filename\n";
	}

	function makeSizes($item,$collection) {
		$dest = $collection->path_to_media_files . "/pdf/" . $item->serial_number . '.pdf';
		$this->copyTo($dest);
		$media_file = new Dase_DB_MediaFile;
		$media_file->item_id = $item->id;
		$media_file->filename = $item->serial_number . '.pdf';
		$media_file->file_size = $this->file_size;
		$media_file->mime_type = $this->mime_type;
		$media_file->size = 'pdf';
		$media_file->p_collection_ascii_id = $collection->ascii_id;
		$media_file->p_serial_number = $item->serial_number;
		$media_file->insert();
		return "created $media_file->filename\n";
	}

}

