<?php

require_once 'Dase/DB/Autogen/XmlCache.php';

class Dase_DB_XmlCache extends Dase_DB_Autogen_XmlCache 
{

	public static function getXml($name,$collection_id = 0,$other_ident = '') {
		$cache = new Dase_DB_XmlCache();
		$cache->name = $name;
		$cache->collection_id = $collection_id;
		$cache->other_ident = $other_ident;
		if ($cache->findOne()) {
			return $cache->text;
		} else {
			return false;
		}
	}

	public static function saveXml($name,$xml,$collection_id = 0,$other_ident = '') {
		//this should really be a generic db-based cache
		$cache = new Dase_DB_XmlCache();
		$cache->name = $name;
		$cache->collection_id = $collection_id;
		$cache->other_ident = $other_ident;
		if ($cache->findOne()) {
			$cache->text = $xml;
			$cache->update();
		} else {
			$cache->text = $xml;
			$cache->is_stale = 0;
			$cache->insert();
		}
	}
}
