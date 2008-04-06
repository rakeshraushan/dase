<?php

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

