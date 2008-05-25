<?php

$routes['search'] = array(
	'itemAsAtom' =>    array (
		'uri_template' => array('search_item','collection/{collection_ascii_id}/search_item'),
		'auth' => 'none',
		'format' => 'atom'
	),
	'opensearch' =>    array (
		'uri_template' => array('search','search/md5/{md5_hash}','collection/{collection_ascii_id}/search'),
		'auth' => 'none',
		'format' => 'atom'
	),
	'item' =>    array (
		'uri_template' => array('search_item','collection/{collection_ascii_id}/search_item'),
		'auth' => 'none',
	),
	'search' =>    array (
		'uri_template' => array('search','collection/{collection_ascii_id}/search'),
		'auth' => 'user',
	),
	'showSql' =>    array (
		'uri_template' => 'sql/collection/{collection_ascii_id}/search',
		'auth' => 'superuser',
	),
);

