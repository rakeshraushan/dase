<?php

require_once 'Dase/DB/Autogen/UtilCache.php';

class Dase_DB_UtilCache extends Dase_DB_Autogen_UtilCache 
{
	public static function getText($ascii_id,$collection_ascii_id = '') {
		$cache = new Dase_DB_UtilCache();
		$cache->ascii_id = $ascii_id;
		if ($collection_ascii_id) {
			$cache->collection_ascii_id = $collection_ascii_id;
		}
		if ($cache->findOne()) {
			//is it fresh???
			if (($cache->timestamp + $cache->ttl) > time()) {	
				return $cache->text;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	public static function saveText($ascii_id,$collection_ascii_id = '',$text,$ttl = 3600) {
		//this should really be a generic db-based cache
		$cache = new Dase_DB_UtilCache();
		$cache->ascii_id = $ascii_id;
		$cache->collection_ascii_id = $collection_ascii_id;
		if ($cache->findOne()) {
			$cache->text = $text;
			$cache->ttl = $ttl;
			$cache->timestamp = time();
			$cache->update();
		} else {
			$cache->text = $text;
			$cache->ttl = $ttl;
			$cache->timestamp = time();
			$cache->insert();
		}
	}
}
