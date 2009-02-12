<?php

require_once 'Dase/DBO/Autogen/SearchCache.php';

class Dase_DBO_SearchCache extends Dase_DBO_Autogen_SearchCache 
{
	public static function get($hash)
	{
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

	/** check db to get file cache ids to expire */
	public static function deleteRecent()
	{
		$one_hour_ago = date(DATE_ATOM,time()-(60*60));
		$search_caches = new Dase_DBO_SearchCache;
		$search_caches->addWhere('timestamp',$one_hour_ago,'>');
		$count = 0;
		foreach ($search_caches->find() as $sc) {
			Dase_Cache_File::expungeByHash($sc->search_md5);
			$sc->delete();
			$count++;
		}
		return $count;
	}
}
