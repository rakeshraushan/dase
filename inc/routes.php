<?php

include 'routes/collection.php';
include 'routes/item.php';
include 'routes/atom.php';

$routes['sandbox'] = array (
	'monitor' => array (
		'uri_template' => 'sandbox/monitor',
		'auth' => 'superuser',
	),
	'calendar' => array (
		'uri_template' => 'sandbox/calendar',
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

$routes['search'] = array(
	'itemAsAtom' =>    array (
		'uri_template' => array('atom/search_item','atom/collection/{collection_ascii_id}/search_item'),
		'auth' => 'none',
		'mime' => 'application/atom+xml',
	),
	'opensearch' =>    array (
		'uri_template' => array('atom/search','atom/search/md5/{md5_hash}','atom/collection/{collection_ascii_id}/search'),
		'auth' => 'none',
		'mime' => 'application/atom+xml',
	),
	'item' =>    array (
		'uri_template' => array('search_item','collection/{collection_ascii_id}/search_item'),
		'auth' => 'none',
	),
	'search' =>    array (
		'uri_template' => array('search','collection/{collection_ascii_id}/search'),
		'auth' => 'user',
	),
	'sql' =>    array (
		'uri_template' => 'sql/collection/{collection_ascii_id}/search',
		'auth' => 'superuser',
	),
);

//think about tag privacy here
$routes['tag'] = array(
	'asAtom' =>    array (
		'uri_template' => array(
			'atom/user/{eid}/tag/{ascii_id}',
			'atom/user/{eid}/tag/id/{id}'
		),
		'auth' => 'none',
		//'auth' => 'token',
		'mime' => 'application/atom+xml',
	),
	'get' =>    array (
		'uri_template' => 'user/{eid}/tag/{ascii_id}',
		'auth' => 'eid',
	),
	'item' =>    array (
		'uri_template' => 'user/{eid}/tag/{ascii_id}/{tag_item_id}',
		'auth' => 'eid',
	),
	'itemAsAtom' =>    array (
		'uri_template' => 'atom/user/{eid}/tag/{ascii_id}/{tag_item_id}',
		//'auth' => 'token',
		'auth' => 'none',
		'mime' => 'application/atom+xml',
	),
	'saveToTag' =>    array (
		'uri_template' => 'user/{eid}/tag/{tag_ascii_id}',
		'auth' => 'eid',
		'method' => 'post',
		'mime' => 'text/plain',
	),
	'removeItems' => array (
		'uri_template' => 'user/{eid}/tag/{tag_ascii_id}/remove_items',
		'auth' => 'eid',
		'method' => 'post',
		'mime' => 'text/plain',
	),
);


$routes['user'] = array(
	'dataAsJson' =>    array (
		'uri_template' => 'json/user/{eid}/data',
		'auth' => 'eid',
		'mime' => 'application/json',
		'nocache' => 'custom',
	),
	'cartAsJson' =>    array (
		'uri_template' => 'json/user/{eid}/cart',
		'auth' => 'eid',
		'mime' => 'application/json',
	),
	'adminCollectionsAsJson' =>    array (
		'uri_template' => 'json/user/{eid}/collections',
		'auth' => 'eid',
		'mime' => 'application/json',
	),
	'initiateLogin' =>    array (
		'uri_template' => 'login',
		'auth' => 'none',
	),
	'processLogin' =>    array (
		'uri_template' => 'login',
		'method' => 'post',
		'auth' => 'none',
	),
	'finishLogin' =>    array (
		'uri_template' => 'login/{eid}',
		'auth' => 'none',
	),
	'logoff' =>    array (
		'uri_template' => 'logoff',
		'auth' => 'none',
	),
	'cart' =>    array (
		'uri_template' => 'user/{eid}/cart',
		'auth' => 'eid',
	),
	'settings' =>    array (
		'uri_template' => 'user/{eid}',
		'auth' => 'eid',
	),
	'addCartItem' =>    array (
		'uri_template' => 'user/{eid}/cart',
		'auth' => 'eid',
		'method' => 'post',
	),
	'deleteTagItem' =>    array (
		'uri_template' => 'user/{eid}/tag_items/{tag_item_id}',
		'auth' => 'eid',
		'method' => 'delete',
	),
);

$routes['attribute'] = array( 
	'attributeValuesAsHtml' =>    array (
		'uri_template' => 'collection/{collection_ascii_id}/attribute/{attribute_ascii_id}',
		'auth' => 'none',
	),
);

$routes['media'] = array( 
	'asAtom' =>    array (
		//mle = 'media link entry'
		'uri_template' => 'mle/{collection_ascii_id}/{serial_number}/{size}',
		'auth' => 'none',
		'mime' => 'application/atom+xml',
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
	'phpinfo' =>    array (
		'uri_template' => 'phpinfo',
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

