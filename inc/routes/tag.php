<?php

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

