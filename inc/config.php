<?php

$conf['db']['type'] = 'sqlite';
$conf['db']['path'] = '/var/www-data/dase/dase.db';  //sqlite only
$conf['db']['host'] = 'localhost';
$conf['db']['name'] = 'dase';
$conf['db']['user'] = 'username';
$conf['db']['pass'] = 'password';

$conf['superuser'][] = 'pkeane';
$conf['superuser'][] = 'rru62';

//used to create only-known-by-server security hash
$conf['token'] = '
	----
	Let us go then, you and I,	
	When the evening is spread out against the sky	
	Like a patient etherised upon a table;	
    Let us go, through certain half-deserted streets,	
	The muttering retreats
	Of restless nights in one-night cheap hotels	
	And sawdust restaurants with oyster-shells:	
	Streets that follow like a tedious argument	
	Of insidious intent	
	To lead you to an overwhelming question 
	Oh, do not ask, "What is it?"	
	Let us go and make our visit.'
   	. date('Ymd',time()); //changes every day

//POST/PUT/DELETE token:	
$conf['ppd_token'] = "
	When you're lost in the rain in Juarez 
	and it's Easter time, too." 
	. date('Ymd',time()); //changes every day

//used ONLY as a default when we create a new collection
$conf['path_to_media'] = '/mnt/www-data/dase/media';

//a place to store metadata of deleted items (just-in-case)
$conf['graveyard'] = "/mnt/home/pkeane/dase_graveyard";

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

$conf['sizes'] = array(
	'400',
	'aiff',
	'archive',
	'css',
	'deleted',
	'doc',
	'full',
	'gif',
	'html',
	'jpeg',
	'large',
	'medium',
	'mp3',
	'pdf',
	'png',
	'quicktime',
	'quicktime_stream',
	'raw',
	'small',
	'text',
	'thumbnails',
	'tiff',
	'uploaded_files',
	'wav',
	'xml',
	'xslt',
);
