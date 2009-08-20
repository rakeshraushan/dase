<?php

class Dase_Exception extends Exception {}

class Dase
{
	public static function run($config)
	{
		$db = new Dase_DB($config);

		$cache = Dase_Cache::get(CACHE_TYPE);

		//refreshed once per hour
		//do not forget to expunge when necessary
		$serialized_app_data = $cache->getData('app_data',3600);
		if (!$serialized_app_data) {
			$c = new Dase_DBO_Collection($db);
			$colls = array();
			$acl = array();
			foreach ($c->find() as $coll) {
				$colls[$coll->ascii_id] = $coll->collection_name;
				$acl[$coll->ascii_id] = $coll->visibility;
			}
			$app_data['collections'] = $colls;
			$app_data['media_acl'] = $acl;
			$cache->setData('app_data',serialize($app_data));
		} else {
			$app_data = unserialize($serialized_app_data);
		}

		$GLOBALS['app_data'] = $app_data;

		$r = new Dase_Http_Request($config->getAppSettings('default_handler'));
		$r->initUser($db);
		$r->initCache(Dase_Cache::get(CACHE_TYPE));
		$r->initCookie($config->getAuth('token'));
		$r->initAuth($config->getAuth());
		$r->initPlugin($config->getCustomHandlers());
		$r->logRequest();

		$handler = $r->getHandlerObject($db,$config);
		$handler->dispatch($r);
	}
}

