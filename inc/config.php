<?php

$conf['db']['type'] = 'mysql';
$conf['db']['path'] = '/var/www-data/dase/dase.db';  //sqlite only
$conf['db']['host'] = 'localhost';
$conf['db']['name'] = 'dase';
$conf['db']['user'] = 'username';
$conf['db']['pass'] = 'password';
$conf['db']['table_prefix'] = '';

$conf['app']['main_title'] = "My DASe Archive";

//must be apache writeable
//only set these (in local_config) to override 
//default <base_path>/files/...
//$conf['app']['media_dir'] = '/usr/local/dase/media';
//$conf['app']['cache_dir'] = '/usr/local/dase/cache';
//$conf['app']['log_dir'] = '/usr/local/dase/log';

//path to imagemagick convert
$conf['app']['convert'] = '/usr/bin/convert';

//maximum no. of items displayed on a search result page
$conf['app']['max_items'] = 30;

//handler that gets invoked when APP_ROOT is requested
//$conf['default_handler'] = 'collections';
$conf['app']['default_handler'] = 'install';

//cache can be file or memcached (only 'file' is implemented) 
$conf['app']['cache_type'] = 'file';

//default <base_path>/files/<cron_output_dir>
//note that cron user should be apache or in apache group
$conf['app']['cron_output_dir'] = 'cron_output';

//eid & admin password
//$conf['auth']['superuser']['<username>'] = '<password>';
//$conf['auth']['serviceuser']['prop'] = 'ok';
$conf['auth']['service_token'] = " ***thecatinthehatcameback*** ";

//define module handlers (can override existing handler)
//$conf['request_handler']['<handler>'] = '<module_name>';
//$conf['request_handler']['login'] = 'openid';
$conf['request_handler']['db'] = 'dbadmin';
$conf['request_handler']['install'] = 'install';
$conf['request_handler']['grants'] = 'itsprop';

//used to create only-known-by-server security hash
$conf['auth']['token'] = '++foxinsocks++'.date('Ymd',time());

//POST/PUT/DELETE token:	
$conf['auth']['ppd_token'] = "--greeneggsandham--".date('Ymd',time());


