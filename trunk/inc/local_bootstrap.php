<?php

/* this file is called after the db is set up, so it can 
 * assume the existence of $db and $config objects
 */

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
		//backwards compat
		$acl[$coll->ascii_id.'_collection'] = $coll->visibility;
	}
	$app_data['collections'] = $colls;
	$app_data['media_acl'] = $acl;
	$cache->setData('app_data',serialize($app_data));
} else {
	$app_data = unserialize($serialized_app_data);
}

$GLOBALS['app_data'] = $app_data;


