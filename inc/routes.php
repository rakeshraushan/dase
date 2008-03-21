<?php

$routes['atom'] = array (
	'get_post' =>    array (
		'uri_template' => 'app/collection/{collection_ascii_id}/post',
		'auth' => 'none',
	),
	'get_service' =>    array (
		'uri_template' => 'app/collection/{collection_ascii_id}/service',
		'auth' => 'none',
		'mime' => 'application/atomsvc+xml',
	),
	'get_categories_xml' =>    array (
		'uri_template' => 'app/collection/{collection_ascii_id}/categories',
		'auth' => 'none',
	),
	'get_posts' =>    array (
		'uri_template' => 'app/collection/{collection_ascii_id}/posts',
		'auth' => 'none',
	),
	'get_attachment' =>    array (
		'uri_template' => 'app/collection/{collection_ascii_id}/attachment',
		'auth' => 'none',
	),
	'get_file' =>    array (
		'uri_template' => 'app/collection/{collection_ascii_id}/attachment/file',
		'auth' => 'none',
	),
	'create_post' =>    array (
		'uri_template' => 'app/collection/{collection_ascii_id}/posts',
		'auth' => 'none',
		'method' => 'post',
	),
	'create_attachment' =>    array (
		'uri_template' => 'app/collection/{collection_ascii_id}/attachments',
		'auth' => 'none',
		'method' => 'post',
	),
	'put_post' =>    array (
		'uri_template' => 'app/collection/{collection_ascii_id}/post',
		'auth' => 'none',
		'method' => 'put',
	),
	'put_file' =>    array (
		'uri_template' => 'app/collection/{collection_ascii_id}/attachment/file',
		'auth' => 'none',
		'method' => 'put',
	),
	'put_attachment' =>    array (
		'uri_template' => 'app/collection/{collection_ascii_id}/attachment',
		'auth' => 'none',
		'method' => 'put',
	),
	'delete_post' =>    array (
		'uri_template' => 'app/collection/{collection_ascii_id}/post',
		'auth' => 'none',
		'method' => 'delete',
	),
	'delete_file' =>    array (
		'uri_template' => 'app/collection/{collection_ascii_id}/attachment/file',
		'auth' => 'none',
		'method' => 'delete',
	),
	'delete_attachment' =>    array (
		'uri_template' => 'app/collection/{collection_ascii_id}/attachment',
		'auth' => 'none',
		'method' => 'delete',
	),
);

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

$routes['collection'] = array (
	'listAll' =>    array (
		'uri_template' => array('collection/list','collections','home',''),
		'auth' => 'none',
	),
	'asAtom' =>    array (
		'uri_template' => 'atom/collection/{collection_ascii_id}',
		'auth' => 'none',
		'mime' => 'application/atom+xml',
	),
	'listAsAtom' =>    array (
		'uri_template' => array('atom/collections','atom'),
		'auth' => 'none',
		'mime' => 'application/atom+xml',
	),
	'attributesAsAtom' =>    array (
		'uri_template' => 'atom/collection/{collection_ascii_id}/attributes/public',
		'auth' => 'none',
	),
	'attributesAsHtml' =>    array (
		'uri_template' => 'collection/{collection_ascii_id}/attributes/public',
		'auth' => 'none',
	),
	'adminAttributesAsHtml' =>    array (
		'uri_template' => 'collection/{collection_ascii_id}/attributes/admin',
		'auth' => 'none',
	),
	'browse' =>    array (
		'uri_template' => 'collection/{collection_ascii_id}',
		'auth' => 'user',
	),
	'itemTalliesAsJson' =>    array (
		'uri_template' => 'json/item_tallies',
		'auth' => 'none',
		'mime' => 'application/json',
	),
	'attributeTalliesAsJson' =>    array (
		'uri_template' => 'json/collection/{collection_ascii_id}/attribute_tallies',
		'auth' => 'user',
		'mime' => 'application/json',
	),
	'adminAttributeTalliesAsJson' =>    array (
		'uri_template' => 'json/collection/{collection_ascii_id}/admin_attribute_tallies',
		'auth' => 'user',
		'mime' => 'application/json',
	),
	'buildIndex' =>    array (
		'uri_template' => 'ollection/buildInde',
		'auth' => 'admin',
	),
	'attributesAsJson' =>    array (
		'uri_template' => 'json/collection/{collection_ascii_id}/attributes',
		'auth' => 'user',
		'mime' => 'application/json',
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
		'auth' => 'token',
		'mime' => 'application/atom+xml',
	),
);

$routes['item'] = array( 
	'asAtom' =>    array (
		'uri_template' => 'atom/collection/{collection_ascii_id}/{serial_number}',
		'auth' => 'none',
		'mime' => 'application/atom+xml',
	),
	'display' =>    array (
		'uri_template' => 'collection/{collection_ascii_id}/{serial_number}',
		'auth' => 'user',
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
	'tag' =>    array (
		'uri_template' => 'user/{eid}/tag/{ascii_id}',
		'auth' => 'eid',
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

