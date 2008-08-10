<?php

require_once 'Dase/DBO/Autogen/MediaAttribute.php';

class Dase_DBO_MediaAttribute extends Dase_DBO_Autogen_MediaAttribute 
{
	public static $common = array(
		'client_ip_address',
		'height',
		'width',
		'duration',
		'bitrate',
		'frame_rate',
		'sampling_rate',
		'language',
		'file_size',
		'md5',
		'mime_type',
		'updated',
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
		//assume a leading 'admin_' can be stripped
		$term = str_replace('admin_','',$term);
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
