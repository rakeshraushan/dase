<?php

include DASE_PATH.'/lib/getid3/getid3.php';

class Dase_File_Audio extends Dase_File
{
	protected $metadata = array();

	function __construct($file,$mime='')
	{
		parent::__construct($file,$mime);
	}

	function getMetadata()
	{
		$this->metadata = parent::getMetadata();
		$getid3 = new getid3;
		$getid3->encoding = 'UTF-8';
		try {
			$getid3->Analyze($this->filepath);
			$id3 = $getid3->info;
		}
		catch (Exception $e) {
			echo 'An error occured: ' .  $e->message;
		}
		if (is_array($id3)) {
			$this->metadata['duration'] =  $id3['playtime_seconds'];
			$this->metadata['bitrate'] =  $id3['bitrate'];
			$this->metadata['channels'] = $id3['audio']['channels'];
			$this->metadata['samplingrate'] = $id3['audio']['sample_rate'];
			$this->metadata['audio_title'] = $id3['comments']['title'][0]; 
			$this->metadata['audio_artist'] = $id3['comments']['artist'][0];
			if (isset($id3['comments']['comment'])) {
				$this->metadata['audio_comment'] = $id3['comments']['comment'][0];
			}
			if (isset($id3['comments']['album'])) {
				$this->metadata['audio_album'] = $id3['comments']['album'][0];
			}
			if (isset($id3['comments']['year'])) {
				$this->metadata['audio_year'] = $id3['comments']['year'][0];
			}
			if (isset($id3['comments']['encoded_by'])) {
				$this->metadata['audio_encoded_by'] = $id3['comments']['encoded_by'][0];
			}
			if (isset($id3['comments']['track'])) {
				$this->metadata['audio_track'] = $id3['comments']['track'][0];
			}
			if (isset($id3['comments']['genre'])) {
				$this->metadata['audio_genre'] = $id3['comments']['genre'][0];
			}
			if (isset($id3['comments']['totaltracks'])) {
				$this->metadata['audio_totaltracks'] = $id3['comments']['totaltracks'][0];
			}
		}
		return $this->metadata;
	}

	public function addToCollection($item,$check_for_dups) 
	{
		$c = $item->getCollection();
		$this->getMetadata();
		//prevents 2 files in same collection w/ same md5
		if ($check_for_dups) {
			$mf = new Dase_DBO_MediaFile;
			$mf->p_collection_ascii_id = $c->ascii_id;
			$mf->md5 = $this->metadata['md5'];
			if ($mf->findOne()) {
				throw new Exception('duplicate file');
			}
		}
		$ext = Dase_File::$types_map[$this->metadata['mime_type']]['ext'];
		$target = Dase_Config::get('path_to_media').'/'.$c->ascii_id.'/'.$this->size.'/'.$item->serial_number.'.'.$ext;
		//should this be try-catch?
		if ($this->copyTo($target)) {
			$media_file = new Dase_DBO_MediaFile;
			//follows search.yahoo.com/mrss attributes
			$meta = array(
				'file_size','height','width','mime_type','updated','md5'
			);
			foreach ($meta as $term) {
				if (isset($this->metadata[$term])) {
					$media_file->$term = $this->metadata[$term];
				}
			}
			$media_file->item_id = $item->id;
			$media_file->filename = $item->serial_number.'.mp3';
			$media_file->size = $this->size;
			$media_file->p_serial_number = $item->serial_number;
			$media_file->p_collection_ascii_id = $c->ascii_id;
			$media_file->insert();
			foreach ($this->metadata as $term => $text) {
				//will only insert item metadata when attribute name matches 'admin_'+att_name
				$item->setValue('admin_'.$term,$text);
			}
		}
		$this->makeThumbnail($item);
		$this->makeViewitem($item);
		return $media_file;
	}

	function makeThumbnail($item)
	{
		$c = $item->getCollection();
		if (!file_exists(Dase_Config::get('path_to_media').'/'.$c->ascii_id . "/thumbnail/audio.jpg")) {
			copy(DASE_PATH . '/www/images/thumb_icons/audio.jpg',Dase_Config::get('path_to_media').'/'.$c->ascii_id . '/thumbnail/audio.jpg');
		}
		$media_file = new Dase_DBO_MediaFile;
		$media_file->item_id = $item->id;
		$media_file->filename = 'audio.jpg';
		$media_file->file_size = filesize(Dase_Config::get('path_to_media').'/'.$c->ascii_id . '/thumbnail/audio.jpg');
		$media_file->md5 = md5_file(Dase_Config::get('path_to_media').'/'.$c->ascii_id . '/thumbnail/audio.jpg');
		$media_file->updated = date(DATE_ATOM);
		$media_file->width = 80;
		$media_file->height = 80;
		$media_file->mime_type = 'image/jpeg';
		$media_file->size = 'thumbnail';
		$media_file->p_collection_ascii_id = $c->ascii_id;
		$media_file->p_serial_number = $item->serial_number;
		$media_file->insert();
		Dase_Log::info("created $media_file->size $media_file->filename");
	}

	function makeViewitem($item)
	{
		$c = $item->getCollection();
		if (!file_exists(Dase_Config::get('path_to_media').'/'.$c->ascii_id . "/viewitem/audio.jpg")) {
			copy(DASE_PATH . '/www/images/thumb_icons/audio.jpg',Dase_Config::get('path_to_media').'/'.$c->ascii_id . '/viewitem/audio.jpg');
		}
		$media_file = new Dase_DBO_MediaFile;
		$media_file->item_id = $item->id;
		$media_file->filename = 'audio.jpg';
		$media_file->file_size = filesize(Dase_Config::get('path_to_media').'/'.$c->ascii_id . '/viewitem/audio.jpg');
		$media_file->md5 = md5_file(Dase_Config::get('path_to_media').'/'.$c->ascii_id . '/viewitem/audio.jpg');
		$media_file->updated = date(DATE_ATOM);
		$media_file->width = 80;
		$media_file->height = 80;
		$media_file->mime_type = 'image/jpeg';
		$media_file->size = 'viewitem';
		$media_file->p_collection_ascii_id = $c->ascii_id;
		$media_file->p_serial_number = $item->serial_number;
		$media_file->insert();
		Dase_Log::info("created $media_file->size $media_file->filename");
	}
}

