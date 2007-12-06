<?php

//NOTE WELL!!!:
//note that we ONLY use the request_url so the IE cache-busting
//timestamp is ignored.  We can have a long ttl here because ALL
//operations that change user date are required to expire this cache
//NOTE: request_url is 'json/user/{eid}/data'


//need to have SOME data returned if there is no user

if (!isset($_SERVER['PHP_AUTH_USER'])) {
	exit;
}

if (!isset($params['eid'])) {
	$params['eid'] = $_SERVER['PHP_AUTH_USER'];
}

$cache = new Dase_FileCache($params['eid'] . '_data');
$page = $cache->get();
if (!$page) {
	$cache->setTimeToLive(300);
	$page = Dase_User::get($params['eid'])->getData();
	$cache->set($page);
}
//passing false as second param 
//means cache will NOT be reset
Dase::display($page,false);
