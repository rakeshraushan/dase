<?php

$conf['path_to_media'] = '/mnt/www-data/dase/media';

$conf['superuser'][] = 'pkeane';

$conf['token'] = 'secret';

//model can be db, xml, or remote
$conf['model'] = 'db';

$conf['db']['type'] = 'sqlite';
$conf['db']['path'] = '/var/www-data/dase/dase.db';  //sqlite only
$conf['db']['host'] = 'localhost';
$conf['db']['name'] = 'dase';
$conf['db']['user'] = 'username';
$conf['db']['pass'] = 'password';

$conf['remote']['laits']['url'] = 'http://dase.laits.utexas.edu/api/v1/';
$conf['remote']['laits']['username'] = 'http://dase.laits.utexas.edu/api/v1/';
$conf['remote']['laits']['password'] = 'http://dase.laits.utexas.edu/api/v1/';

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
