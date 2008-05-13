<?php

//think about tag privacy here
$routes['tag'] = array(
	'asAtom' =>    array (
		'uri_template' => array(
			'atom/user/{eid}/tag/{tag_ascii_id}',
			'atom/user/{eid}/tag/id/{id}'
		),
		'auth' => 'http',
	),
	'get' =>    array (
		'uri_template' => 'user/{eid}/tag/{tag_ascii_id}',
		'auth' => 'eid',
	),
	'item' =>    array (
		'uri_template' => 'user/{eid}/tag/{tag_ascii_id}/{tag_item_id}',
		'auth' => 'eid',
	),
	'itemAsAtom' =>    array (
		'uri_template' => 'atom/user/{eid}/tag/{tag_ascii_id}/{tag_item_id}',
		'auth' => 'http',
	),
	'saveToTag' =>    array (
		'uri_template' => 'user/{eid}/tag/{tag_ascii_id}',
		'auth' => 'eid',
		'method' => 'post',
	),
	'removeItems' => array (
		'uri_template' => 'user/{eid}/tag/{tag_ascii_id}/remove_items',
		'auth' => 'eid',
		'method' => 'post',
	),
);

