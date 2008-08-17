<?php

$conf['db']['type'] = 'mysql';
$conf['db']['path'] = '/var/www-data/dase/dase.db';  //sqlite only
$conf['db']['host'] = 'localhost';
$conf['db']['name'] = 'dase';
$conf['db']['user'] = 'username';
$conf['db']['pass'] = 'password';

//eid & admin password
//$conf['superuser']['<username>'] = '<password>';

//define module handlers (can override existing handler)
//$conf['handler']['<handler>'] = '<module_name>';
//$conf['handler']['login'] = 'openid';
$conf['handler']['db'] = 'dbadmin';
$conf['handler']['install'] = 'install';

//used to create only-known-by-server security hash
$conf['token'] = '++foxinsocks++';

//POST/PUT/DELETE token:	
$conf['ppd_token'] = "--greeneggsandham--";

//path to imagemagick convert
$conf['convert'] = '/usr/bin/convert';

//must be apache group writeable
$conf['path_to_media'] = '/var/www/data/dase/media';

//a place to archive metadata of deleted items 
//must be apache group writeable
$conf['graveyard'] = "/var/www/data/dase/graveyard";

//maximum no. of items displayed on a search result page
$conf['max_items'] = 30;

//handler that gets invoked when APP_ROOT is requested
//$conf['default_handler'] = 'collections';
$conf['default_handler'] = 'install';

//local_config CAN OVERRIDE any of the above values
if (file_exists( DASE_PATH . '/inc/local_config.php')) {
	include DASE_PATH . '/inc/local_config.php';
}

//causes tokens to change daily
$conf['token'] = $conf['token'].date('Ymd',time()); 
$conf['ppd_token'] = $conf['ppd_token'].date('Ymd',time());

//mime types that collections accept
$conf['media_types'][] = 'image/*';
$conf['media_types'][] = 'audio/*';
$conf['media_types'][] = 'video/*';
$conf['media_types'][] = 'application/pdf';

//cache can be file or memcached (only 'file' is implemented) 
$conf['cache'] = 'file';

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

