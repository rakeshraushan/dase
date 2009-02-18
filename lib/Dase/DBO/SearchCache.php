<?php

require_once 'Dase/DBO/Autogen/SearchCache.php';

class Dase_DBO_SearchCache extends Dase_DBO_Autogen_SearchCache 
{
	public static function get($db,$hash)
	{
		$result = array();
		$cache = new Dase_DBO_SearchCache($db);
		$cache->search_md5 = $hash;
		if ($cache->findOne()) {
			$result = unserialize($cache->item_id_string);
			$result['timestamp'] = $cache->timestamp;
			$result['hash'] = $cache->search_md5;
		}
		return $result;
	}

	/** check db to get file cache ids to expire */
	public static function deleteRecent($db,$r)
	{
		$one_hour_ago = date(DATE_ATOM,time()-(60*60));
		$search_caches = new Dase_DBO_SearchCache($db);
		$search_caches->addWhere('timestamp',$one_hour_ago,'>');
		$count = 0;
		$cache = new Dase_Cache($r->config->get('cache'),$r->config->get('base_path').'/'.$r->config->get('cache_dir'));
		foreach ($search_caches->find() as $sc) {
			$cache->expungeByHash($sc->search_md5);
			$sc->delete();
			$count++;
		}
		return $count;
	}
}
