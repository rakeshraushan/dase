<?php

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
		//todo: id3 will ONLY be available as a plugin from now on 8/9/08
		/*
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
			$this->metadata['admin_duration'] =  $id3['playtime_seconds'];
			$this->metadata['admin_audio_bitrate'] =  $id3['bitrate'];
			$this->metadata['admin_audio_channel_mode'] = $id3['audio']['channelmode'];
			$this->metadata['admin_audio_sampling_rate'] = $id3['audio']['sample_rate'];
			$this->metadata['admin_audio_time'] = $id3['playtime_string'];
			$this->metadata['admin_audio_title'] = $id3['comments']['title'][0]; 
			$this->metadata['admin_audio_artist'] = $id3['comments']['artist'][0];
			if (isset($id3['comments']['comment'])) {
				$this->metadata['admin_audio_comment'] = $id3['comments']['comment'][0];
			}
			if (isset($id3['comments']['album'])) {
				$this->metadata['admin_audio_album'] = $id3['comments']['album'][0];
			}
			if (isset($id3['comments']['year'])) {
				$this->metadata['admin_audio_year'] = $id3['comments']['year'][0];
			}
			if (isset($id3['comments']['encoded_by'])) {
				$this->metadata['admin_audio_encoded_by'] = $id3['comments']['encoded_by'][0];
			}
			if (isset($id3['comments']['track'])) {
				$this->metadata['admin_audio_track'] = $id3['comments']['track'][0];
			}
			if (isset($id3['comments']['genre'])) {
				$this->metadata['admin_audio_genre'] = $id3['comments']['genre'][0];
			}
			if (isset($id3['comments']['totaltracks'])) {
				$this->metadata['admin_audio_totaltracks'] = $id3['comments']['totaltracks'][0];
			}
		}
		 */
		return $this->metadata;
	}

	function makeThumbnail($item,$collection)
	{
		if (!file_exists($collection->path_to_media_files . "/thumbnails/audio.jpg")) {
			copy(DASE_PATH . '/images/thumb_icons/audio.jpg',$collection->path_to_media_files . '/thumbnails/audio.jpg');
		}
		$media_file = new Dase_DBO_MediaFile;
		$media_file->item_id = $item->id;
		$media_file->filename = 'audio.jpg';
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
		if (!file_exists($collection->path_to_media_files . "/400/audio.jpg")) {
			copy(DASE_PATH . '/images/thumb_icons/audio.jpg',$collection->path_to_media_files . '/400/audio.jpg');
		}
		$media_file = new Dase_DBO_MediaFile;
		$media_file->item_id = $item->id;
		$media_file->filename = 'audio.jpg';
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
		$dest = $collection->path_to_media_files . "/mp3/" . $item->serial_number . '.mp3';
		$this->copyTo($dest);
		$media_file = new Dase_DBO_MediaFile;

		foreach ($this->getMetadata() as $term => $value) {
			$media_file->addMetadata($term,$value);
		}

		$media_file->item_id = $item->id;
		$media_file->filename = $item->serial_number . '.mp3';
		$media_file->file_size = $this->file_size;
		$media_file->mime_type = $this->mime_type;
		$media_file->size = 'mp3';
		$media_file->p_collection_ascii_id = $collection->ascii_id;
		$media_file->p_serial_number = $item->serial_number;
		$media_file->insert();
		Dase_Log::info("created $media_file->size $media_file->filename");
	}

}

