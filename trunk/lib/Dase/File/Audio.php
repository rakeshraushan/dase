<?php

class Dase_File_Audio extends Dase_File
{
	protected $metadata = array();

	function __construct($file) {
		parent::__construct($file);
	}

	function getMetadata() {
		$this->metadata = parent::getMetadata();
		$getid3 = new getID3;
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
		return $this->metadata;
	}
}

