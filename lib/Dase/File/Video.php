<?php

class Dase_File_Video extends Dase_File
{
	protected $metadata = array();

	function __construct($file,$mime='')
	{
		parent::__construct($file,$mime);
	}

	public function addToCollection($item,$check_for_dups) 
	{
		$collection = $item->getCollection();
		$this->getMetadata();

		//prevents 2 files in same collection w/ same md5
		if ($check_for_dups) {
			$mf = new Dase_DBO_MediaFile;
			$mf->p_collection_ascii_id = $collection->ascii_id;
			$mf->md5 = $this->metadata['md5'];
			if ($mf->findOne()) {
				throw new Exception('duplicate file');
			}
		}
		$target = Dase_Config::get('path_to_media').'/'.$collection->ascii_id.'/'.$this->size.'/'.$item->serial_number.'.'.$this->ext;
		if (file_exists($target)) {
			//make a timestamped backup
			copy($target,$target.'.bak.'.time());
		}
		//should this be try-catch?
		if ($this->copyTo($target)) {
			$media_file = new Dase_DBO_MediaFile;
			$meta = array(
				'file_size','height','width','mime_type','updated','md5'
			);
			foreach ($meta as $term) {
				if (isset($this->metadata[$term])) {
					$media_file->$term = $this->metadata[$term];
				}
			}
			$media_file->item_id = $item->id;
			$media_file->filename = $item->serial_number.'.'.$this->ext;
			$media_file->size = $this->size;
			$media_file->p_serial_number = $item->serial_number;
			$media_file->p_collection_ascii_id = $collection->ascii_id;
			$media_file->insert();
			//will only insert item metadata when attribute name matches 'admin_'+att_name
			foreach ($this->metadata as $term => $text) {
				$item->setValue('admin_'.$term,$text);
			}
		}
		$this->makeThumbnail($item);
		$this->makeViewitem($item);
		return $media_file;
	}

	function makeThumbnail($item)
	{
		$collection = $item->getCollection();
		if (!file_exists(Dase_Config::get('path_to_media').'/'.$collection->ascii_id . "/thumbnail/quicktime.jpg")) {
			copy(DASE_PATH . '/www/images/thumb_icons/quicktime.jpg',Dase_Config::get('path_to_media').'/'.$collection->ascii_id . '/thumbnail/quicktime.jpg');
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
		Dase_Log::info("created $media_file->size $media_file->filename");
	}

	function makeViewitem($item)
	{
		$collection = $item->getCollection();
		if (!file_exists(Dase_Config::get('path_to_media').'/'.$collection->ascii_id . "/viewitem/quicktime.jpg")) {
			copy(DASE_PATH . '/www/images/thumb_icons/quicktime.jpg',Dase_Config::get('path_to_media').'/'.$collection->ascii_id . '/viewitem/quicktime.jpg');
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
		Dase_Log::info("created $media_file->size $media_file->filename");
	}

	function getMetadata()
	{
		//todo: figure out what other metadata we should get here
		return parent::getMetadata();
	}

}
