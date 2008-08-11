<?php

require_once 'Dase/DBO/Autogen/MediaAttribute.php';

class Dase_DBO_MediaAttribute extends Dase_DBO_Autogen_MediaAttribute 
{
	public static $common = array(
		'bitrate',
		'client_ip_address',
		'duration',
		'file_size',
		'filename',
		'frame_rate',
		'height',
		'language',
		'md5',
		'mime_type',
		'sampling_rate',
		'title',
		'updated',
		'width',
	);

	public static function seed() 
	{
		foreach (self::$common as $term) {
			Dase_DBO_MediaAttribute::findOrCreate($term);
		}
	}

	public static function get($term) 
	{
		//assume a leading 'admin_' can be stripped
		$term = str_replace('admin_','',$term);
		$ma = new Dase_DBO_MediaAttribute();
		$ma->term = $term;
		if ($ma->findOne()) {
			return $ma;
		} else {
			return false;
		}
	}

	public static function findOrCreate($term) 
	{
		$ma = Dase_DBO_MediaAttribute::get($term);
		if (!$ma) {
			$ma = new Dase_DBO_MediaAttribute;
			$ma->term = $term;
			$ma->label = ucwords(str_replace('_',' ',$term));
			$ma->insert();
		}
		return $ma;
	}
}
