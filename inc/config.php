<?php

$conf['modules']['vrc'] = 'vrc_collection';
$conf['modules']['efossils'] = 'efossils_collection';
$conf['modules']['elucy'] = 'efossils_collection';
$conf['modules']['pkeane'] = 'keanepj_collection';
$conf['modules']['search'] = 1;
$conf['modules']['dbadmin'] = 1;
$conf['modules']['eid_auth'] = 0;
$conf['modules']['starter'] = 0;
$conf['modules']['friesen'] = 'friesen_collection';

$conf['superuser'][] = 'pkeane';

$conf['token'][] = 'secret';

$conf['db_type'] = 'sqlite';
$conf['db_path'] = '/var/www-data/dase/dase.db';  //sqlite only
$conf['db_host'] = 'localhost';
$conf['db_name'] = 'dase';
$conf['db_user'] = 'username';
$conf['db_pass'] = 'password';

$conf['remote']['laits']['url'] = 'http://dase.laits.utexas.edu/api/v1/';
$conf['remote']['laits']['username'] = 'http://dase.laits.utexas.edu/api/v1/';
$conf['remote']['laits']['password'] = 'http://dase.laits.utexas.edu/api/v1/';


include DASE_PATH . '/inc/local_config.php';

//default lookup values

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

$conf['tag_type'][] = 'admin_collection';
$conf['tag_type'][] = 'cart';
$conf['tag_type'][] = 'slideshow';
$conf['tag_type'][] = 'user_collection';
$conf['tag_type'][] = 'user_metadata';
