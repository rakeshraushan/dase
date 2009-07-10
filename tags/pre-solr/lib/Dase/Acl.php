<?php

class Dase_Acl
{
	//access key: 
	//0: anyone, anywhere,anytime
	//1: must be a valid 'user'
	//2: must have collection-specific privileges

	public static $sizes = array(
		'aiff' => 2,
		'archive' => 1,
		'css' => 1,
		'deleted' => 1,
		'doc' => 1,
		'full' => 1,
		'gif' => 1,
		'html' => 1,
		'jpeg' => 1,
		'large' => 1,
		'medium' => 1,
		'mp3' => 2,
		'pdf' => 1,
		'png' => 1,
		'quicktime' => 2,
		'quicktime_stream' => 2,
		'raw' => 2,
		'small' => 1,
		'text' => 1,
		'thumbnail' => 0,
		'tiff' => 2,
		'uploaded_files' => 2,
		'viewitem' => 1,
		'wav' => 2,
		'xml' => 1,
		'xsl' => 1,
	);

	public static function generate($db)
	{
		$acl = array();
		$colls = new Dase_DBO_Collection($db);
		foreach ($colls->find() as $c) {
			foreach ($c->getManagers() as $m) {
				$acl[$c->ascii_id]['user'][$m->dase_user_eid] = $m->auth_level;
			}
		}
		return $acl;
	}

	public static function getCollectionData($db,$path_to_media)
	{
		$cdata = array();
		$colls = new Dase_DBO_Collection($db);
		foreach ($colls->find() as $c) {
			$cdata[$c->ascii_id]['visibility'] = $c->visibility;
		}
		return $cdata;
	}

	public static function retrieve($url)
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION,1);
		$data = curl_exec($ch);
		curl_close($ch);
		return $data;
	}

	public static function check($db,$coll,$size,$eid=null,$path_to_media)
	{
		$cdata = Dase_Acl::getCollectionData($db,$path_to_media);
		$gate = self::$sizes[$size];
		if (!$gate) {
			return $cdata[$coll]['path_to_media_files'];
		}
		if ($eid && 1 == $gate) { //existence of $eid indicates valid user
			return $cdata[$coll]['path_to_media_files'];
		}
		$acl = Dase_Acl::getAcl();
		if ('public' == $cdata[$coll]['visibility']) {
			return $cdata[$coll]['path_to_media_files'];
		}
		if ('user' == $cdata[$coll]['visibility'] && $eid) {
			return $cdata[$coll]['path_to_media_files'];
		}
		if (isset($acl[$coll]['user'][$eid])) {
			return $cdata[$coll]['path_to_media_files'];
		}
		return false;
	}
}

