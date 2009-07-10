<?php

ini_set('include_path',BASE_PATH.'/lib');

function __autoload($class_name) {
	$class_name = str_replace('_','/',$class_name).'.php';
	@include ($class_name);
}

//set up configuration object
$config = new Dase_Config(BASE_PATH);
$config->load('inc/config.php');
$config->load('inc/local_config.php');

//imagemagick
define('CONVERT',$config->getAppSettings('convert'));

//log file
define('LOG_FILE',$config->getLogDir().'/dase.log');

//log level
define('LOG_LEVEL',Dase_Log::DEBUG);

//media directory
define('MEDIA_DIR',$config->getMediaDir());

//db table prefix
define('TABLE_PREFIX',$config->getDb('table_prefix'));

//cache directory & type
define('CACHE_DIR',$config->getCacheDir());
define('CACHE_TYPE',$config->getCacheType());

//max items diplayed per page
define('MAX_ITEMS',$config->getAppSettings('max_items'));

//main title
define('MAIN_TITLE',$config->getAppSettings('main_title'));

//custom page logo
define('PAGE_LOGO_LINK_TARGET',$config->getLocalSettings('page_logo_link_target'));
define('PAGE_LOGO_SRC',$config->getLocalSettings('page_log_src'));

//timer
define('START_TIME',Dase_Util::getTime());


