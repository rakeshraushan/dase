<?php

$routes['atompub'] = array (
	'getMediaLinkEntry' =>    array (
		'uri_template' => 'edit/{collection_ascii_id}/{serial_number}/media/{size}',
		'auth' => 'http',
	),
	'getMediaResource' =>    array (
		'uri_template' => 'edit-media/{collection_ascii_id}/{serial_number}/media/{size}',
		'auth' => 'http',
	),
	'listCollectionEntries' =>    array (
		'uri_template' => 'edit/{collection_ascii_id}',
		'auth' => 'http',
		'method' => 'get',
	),
	'listItemMedia' =>    array (
		'uri_template' => 'edit/{collection_ascii_id}/{serial_number}/media',
		//'auth' => 'http',
		'auth' => 'none',
		'method' => 'get',
	),
	'createMediaFile' =>    array (
		'uri_template' => 'edit/{collection_ascii_id}/{serial_number}/media',
		'auth' => 'http',
		'method' => 'post',
	),
	'getItemServiceDoc' => array (
		'uri_template' => 'service/{collection_ascii_id}/{serial_number}',
		'auth' => 'http',
	),
	'getCollectionServiceDoc' => array (
		'uri_template' => 'service/{collection_ascii_id}',
		'auth' => 'http',
	),
	'validate' => array (
		'uri_template' => 'validate/{collection_ascii_id}',
		'auth' => 'http',
		'method' => 'post',
	),
	'getItem' =>    array (
		'uri_template' => 'edit/{collection_ascii_id}/{serial_number}',
		'auth' => 'http',
	),
	'createItem' =>    array (
		'uri_template' => 'edit/{collection_ascii_id}',
		'auth' => 'http',
		'method' => 'post',
	),
	'updateItem' => array (
		'uri_template' => 'edit/{collection_ascii_id}/{serial_number}',
		'auth' => 'http',
		'method' => 'put',
	),
	'deleteItem' => array (
		'uri_template' => 'edit/{collection_ascii_id}/{serial_number}',
		'auth' => 'http',
		'method' => 'delete',
	),
	'deleteMediaFile' =>    array (
		'uri_template' => array( 'edit-media/{collection_ascii_id}/{serial_number}/media/{size}', 'edit/{collection_ascii_id}/{serial_number}/media/{size}',),
		'auth' => 'http',
		'method' => 'delete',
	),
);
