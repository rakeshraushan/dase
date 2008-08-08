<?php

//settings in this file are chacked "as needed" (i.e. will not be used
//on every request

$conf['db']['type'] = 'mysql';
$conf['db']['path'] = '/var/www-data/dase/dase.db';  //sqlite only
$conf['db']['host'] = 'localhost';
$conf['db']['name'] = 'dase';
$conf['db']['user'] = 'username';
$conf['db']['pass'] = 'password';

$conf['superuser'][] = 'pkeane';
$conf['superuser'][] = 'rru62';

//define alternative (plugin) handlers
//$conf['handler']['login'] = 'openid';
$conf['handler']['db'] = 'dbadmin';
$conf['handler']['am'] = 'ancientmeso';

//used to create only-known-by-server security hash
$conf['token'] = 'foxinsocks' . date('Ymd',time()); //changes every day

//POST/PUT/DELETE token:	
$conf['ppd_token'] = "
	When you're lost in the rain in Juarez 
	and it's Easter time, too." 
	. date('Ymd',time()); //changes every day

//collection-specific media dirs live under here /<collection_ascii_id>/<size>
$conf['path_to_media'] = '/opt/local/www-data/dase/media';

//a place to store metadata of deleted items (just-in-case)
$conf['graveyard'] = "/opt/local/www-data/dase/graveyard";

//cache can be file or memcached (only 'file' is implemented) 
$conf['cache'] = 'file';

//handler that gets invoked when APP_ROOT is requested
$conf['default_handler'] = 'collections';

//local_config CAN OVERRIDE any of the above values
//this is useful for build scripts to hold db
//usernames & passwords (which shouldn't be checked in)
if (file_exists( DASE_PATH . '/inc/local_config.php')) {
	include DASE_PATH . '/inc/local_config.php';
}

//allow module to overide config 
if (defined('MODULE_PATH') && file_exists( MODULE_PATH . '/inc/config.php')) {
	include(MODULE_PATH . '/inc/config.php');
}	

//maximum no. of items displayed on a search result page
$conf['max_items'] = 30;

//access key: 
//0: anyone, anywhere,anytime
//1: must be a valid 'user'
//2: must have collection-specific privileges

$conf['sizes'] = array(
	'400' => 1,
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
	'thumbnails' => 0,
	'tiff' => 2,
	'uploaded_files' => 2,
	'wav' => 2,
	'xml' => 1,
	'xslt' => 1,
);
