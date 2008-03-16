<?php

class Dase_File_Video extends Dase_File
{
	public function say()
	{
		print "hello world\n";
	}

	function makeThumbnail($item,$collection)
	{
		if (!file_exists($collection->path_to_media_files . "/thumbnails/quicktime.jpg")) {
			copy(DASE_PATH . '/images/thumb_icons/quicktime.jpg',$collection->path_to_media_files . '/thumbnails/quicktime.jpg');
		}
		$media_file = new Dase_DBO_MediaFile;
		$media_file->item_id = $item->id;
		$media_file->filename = 'quicktime.jpg';
		$media_file->width = 80;
		$media_file->height = 80;
		$media_file->mime_type = 'image/jpeg';
		$media_file->size = 'thumbnail';
		$media_file->p_collection_ascii_id = $collection->ascii_id;
		$media_file->p_serial_number = $item->serial_number;
		$media_file->insert();
		return "created $media_file->filename\n";
	}

	function makeViewitem($item,$collection)
	{
		if (!file_exists($collection->path_to_media_files . "/400/quicktime.jpg")) {
			copy(DASE_PATH . '/images/thumb_icons/quicktime.jpg',$collection->path_to_media_files . '/400/quicktime.jpg');
		}
		$media_file = new Dase_DBO_MediaFile;
		$media_file->item_id = $item->id;
		$media_file->filename = 'quicktime.jpg';
		$media_file->width = 80;
		$media_file->height = 80;
		$media_file->mime_type = 'image/jpeg';
		$media_file->size = 'viewitem';
		$media_file->p_collection_ascii_id = $collection->ascii_id;
		$media_file->p_serial_number = $item->serial_number;
		$media_file->insert();
		return "created $media_file->filename\n";
	}

	function getMetadata()
	{
		//figure out what other metadata we should get here
		return parent::getMetadata();
	}

	function makeSizes($item,$collection)
	{
		$dest = $collection->path_to_media_files . "/quicktime/" . $item->serial_number . '.mov';
		$this->copyTo($dest);
		$media_file = new Dase_DBO_MediaFile;
		$media_file->item_id = $item->id;
		$media_file->filename = $item->serial_number . '.mov';
		$media_file->file_size = $this->file_size;
		$media_file->mime_type = $this->mime_type;
		$media_file->size = 'quicktime';
		$media_file->width = 0;
		$media_file->height = 0;
		$media_file->p_collection_ascii_id = $collection->ascii_id;
		$media_file->p_serial_number = $item->serial_number;
		$media_file->insert();
		return "created $media_file->filename\n";
	}
}
