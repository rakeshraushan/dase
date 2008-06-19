<?php

$conf['superuser'][] = 'pkeane';
$conf['superuser'][] = 'rru62';

$conf['token'] = '
	---
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

//see also media/config.php
$conf['path_to_media'] = '/mnt/www-data/dase/media/uploaded_files';
$conf['graveyard'] = "/mnt/home/pkeane/dase_graveyard";

//cache can be file or memcached (only 'file' is implemented) 
$conf['cache'] = 'file';

$conf['login_module'] = 'auth';
$conf['default_handler'] = 'collections';

$conf['db']['type'] = 'sqlite';
$conf['db']['path'] = '/var/www-data/dase/dase.db';  //sqlite only
$conf['db']['host'] = 'localhost';
$conf['db']['name'] = 'dase';
$conf['db']['user'] = 'username';
$conf['db']['pass'] = 'password';

//local_config CAN OVERRIDE any of the above values
//this is useful for build scripts to hold db
//usernames & passwords (which shouldn't be checked in)
if (file_exists( DASE_PATH . '/inc/local_config.php')) {
	include DASE_PATH . '/inc/local_config.php';
}
//also allow modules to overide config if
//request is coming from a module
//note that passwords in modules will
//likely get checked in
//also, login_module cannot be overridden since calls to logoff,login 
//will not create a MODULE_PATH
if (defined('MODULE_PATH') && file_exists( MODULE_PATH . '/inc/config.php')) {
	include(MODULE_PATH . '/inc/config.php');
}	

//default lookup values
//basing id's on array indexes
//here is way too fragile &
//generally a bad idea

$conf['html_input_type'][] = 'checkboxes';
$conf['html_input_type'][] = 'list_box';
$conf['html_input_type'][] = 'non_editable';
$conf['html_input_type'][] = 'radio_buttons';
$conf['html_input_type'][] = 'select_menu';
$conf['html_input_type'][] = 'textarea';
$conf['html_input_type'][] = 'textbox';
$conf['html_input_type'][] = 'textbox_with_dynamic_menu';

$conf['item_status'][] = 'public';
$conf['item_status'][] = 'admin_only';
$conf['item_status'][] = 'marked_for_delete';
$conf['item_status'][] = 'deep_storage';
