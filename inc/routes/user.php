<?php

$routes['user'] = array(
	'dataAsJson' =>    array (
		'uri_template' => 'json/user/{eid}/data',
		'auth' => 'eid',
	),
	'cartAsJson' =>    array (
		'uri_template' => 'json/user/{eid}/cart',
		'auth' => 'eid',
	),
	'adminCollectionsAsJson' =>    array (
		'uri_template' => 'json/user/{eid}/collections',
		'auth' => 'eid',
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
	'getHttpPassword' => array (
		'uri_template' => array( 'user/{eid}/collection/{collection_ascii_id}/auth/{auth_level}','user/{eid}/tag/{tag_ascii_id}/auth/{auth_level}',
		),
		'auth' => 'eid',
	),
);


