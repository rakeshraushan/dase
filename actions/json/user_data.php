<?php

//NOTE WELL!!!:
//note that we ONLY use the request_url so the IE cache-busting
//timestamp is ignored.  We can have a long ttl here because ALL
//operations that change user date are required to expire this cache
//NOTE: request_url is 'json/user/{eid}/data'

$cache = new Dase_FileCache($request_url);
$page = $cache->get();
if (!$page) {
	$cache->setTimeToLive(300);
	$page = Dase_User::get($params['eid'])->getData();
	$cache->set($page);
}
//passing false as second param 
//means cache will NOT be reset
Dase::display($page,false);
