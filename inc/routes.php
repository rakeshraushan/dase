<?php

//create test suite for route dispatching
include 'routes/search.php';
include 'routes/item.php';
include 'routes/collection.php';
include 'routes/app.php';
include 'routes/user.php';
include 'routes/tag.php';

$routes['experimental'] = array (
	'monitor' => array (
		'uri_template' => 'experimental/monitor',
		'auth' => 'superuser',
	),
	'calendar' => array (
		'uri_template' => 'experimental/calendar',
		'auth' => 'superuser',
	),
	'phpinfo' =>    array (
		'uri_template' => 'phpinfo',
		'auth' => 'superuser',
	),
);

$routes['test'] = array (
	'first' => array (
		'uri_template' => 'test/first',
		'auth' => 'superuser',
	),
	'search' => array (
		'uri_template' => 'test/search',
		'auth' => 'superuser',
	),
);

$routes['attribute'] = array( 
	'attributeValuesAsHtml' =>    array (
		'uri_template' => 'collection/{collection_ascii_id}/attribute/{attribute_ascii_id}',
		'auth' => 'none',
	),
);

$routes['manage'] = array(
	'checkRoutes' =>    array (
		'uri_template' => 'badroutes',
		'auth' => 'superuser',
	),
	'exec' =>    array (
		'uri_template' => 'manage/exec',
		'auth' => 'superuser',
	),
	'viewLog' =>    array (
		'uri_template' => 'manage/log/{log_name}',
		'auth' => 'superuser',
	),
	'index' =>    array (
		'uri_template' => 'manage',
		'auth' => 'superuser',
	),
	'modules' =>    array (
		'uri_template' => 'manage/modules',
		'auth' => 'superuser',
	),
	'routes' =>    array (
		'uri_template' => 'manage/routes',
		'auth' => 'superuser',
	),
	'stats' =>    array (
		'uri_template' => 'manage/stats',
		'auth' => 'superuser',
	),
	'monitor' =>    array (
		'uri_template' => 'manage/monitor',
		'auth' => 'superuser',
		'mime' => 'application/xhtml+xml',
	),
);

$routes['admin'] = array(
	'index' =>    array (
		'uri_template' => 'admin/{eid}/{collection_ascii_id}',
		'auth' => 'admin',
	),
	'settings' =>    array (
		'uri_template' => 'admin/{eid}/{collection_ascii_id}/settings',
		'auth' => 'admin',
	),
	'managers' =>    array (
		'uri_template' => 'admin/{eid}/{collection_ascii_id}/managers',
		'auth' => 'admin',
	),
	'item_types' =>    array (
		'uri_template' => 'admin/{eid}/{collection_ascii_id}/item_types',
		'auth' => 'admin',
	),
	'attributes' =>    array (
		'uri_template' => 'admin/{eid}/{collection_ascii_id}/attributes',
		'auth' => 'admin',
	),
	'dataAsJson' =>    array (
		'uri_template' => 'json/collection/{collection_ascii_id}/data/{select}',
		'auth' => 'read',
		'mime' => 'application/json',
		'nocache' => 'custom',
	),
	'setAttributeSortOrder' =>    array (
		'uri_template' => 'admin/{eid/collection_ascii_id/attribute_ascii_id}/XXX/attribute/XXX/sort_order',
		'auth' => 'admin',
		'method' => 'put',
	),
);

$routes['widget'] = array (
	'init' =>    array (
		'uri_template' => 'scripts/widgets.js',
		'auth' => 'none',
		'mime' => 'application/x-javascript',
	),
);

$routes['css'] = array(
	'init' =>    array (
		'uri_template' => 'css/dynamic.css',
		'auth' => 'none',
		'mime' => 'text/css',
	),
);

