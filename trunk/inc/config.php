<?php

$conf['superusers'][] = 'pkeane';
$conf['db_type'] = '@db_type';
$conf['db_host'] = '@db_host';
$conf['db_name'] = '@db_name';
$conf['db_user'] = '@db_user';
$conf['db_pass'] = '@db_pass';


$routes["list_collections"][] = "^collections$";
$routes["list_collections"][] = "^$";
$routes["browse_collection"][] = "^.*_collection$";
$routes["browse_collection"][] = "^.*_archive";
$routes["browse_collection"][] = "^.*_catalog";
$routes["login_form"][] = "^login_form";
$routes["login"][] = "^login";
$routes["logoff"][] = "^logoff";
$routes["view_log"][] = "^view_log/([^/]*)$";
$routes["build_index"][] = "^build_index/([^/]*)$";

