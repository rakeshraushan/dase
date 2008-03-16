<?php

require_once 'Dase/DBO/Autogen/SearchCache.php';

class Dase_DBO_SearchCache extends Dase_DBO_Autogen_SearchCache 
{
	public static function get($hash) {
		$result = array();
		$cache = new Dase_DBO_SearchCache();
		$cache->search_md5 = $hash;
		if ($cache->findOne()) {
			$result = unserialize($cache->item_id_string);
			$result['timestamp'] = $cache->timestamp;
			$result['hash'] = $cache->search_md5;
		}
		return $result;
	}

}
