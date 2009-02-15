<?php

$conf['db']['type'] = 'mysql';
$conf['db']['path'] = '/var/www-data/dase/dase.db';  //sqlite only
$conf['db']['host'] = 'localhost';
$conf['db']['name'] = 'dase';
$conf['db']['user'] = 'username';
$conf['db']['pass'] = 'password';

$conf['app']['table_prefix'] = '';
$conf['app']['main_title'] = "My DASe Archive";

//must be apache writeable
$conf['app']['path_to_media'] = 'media';
$conf['app']['cache_dir'] = 'cache';

//eid & admin password
//$conf['auth']['superuser']['<username>'] = '<password>';
//$conf['auth']['service_token'] = "big-hash-here";
//$conf['auth']['serviceuser']['prop'] = 'ok';

//define module handlers (can override existing handler)
//$conf['handler']['<handler>'] = '<module_name>';
//$conf['handler']['login'] = 'openid';
$conf['handler']['db'] = 'dbadmin';
$conf['handler']['install'] = 'install';
$conf['handler']['grants'] = 'itsprop';

//used to create only-known-by-server security hash
$conf['auth']['token'] = '++foxinsocks++'.date('Ymd',time());

//POST/PUT/DELETE token:	
$conf['auth']['ppd_token'] = "--greeneggsandham--".date('Ymd',time());

//path to imagemagick convert
$conf['app']['convert'] = '/usr/bin/convert';

//maximum no. of items displayed on a search result page
$conf['app']['max_items'] = 30;

//handler that gets invoked when APP_ROOT is requested
//$conf['default_handler'] = 'collections';
$conf['app']['default_handler'] = 'install';

//keep tiffs 
$conf['app']['keep_tiffs'] = true;

//cache can be file or memcached (only 'file' is implemented) 
$conf['app']['cache'] = 'file';


