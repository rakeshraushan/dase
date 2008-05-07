<?php

$routes['atompub'] = array (
	'getMediaLinkEntry' =>    array (
		'uri_template' => 'edit/{collection_ascii_id}/{serial_number}/media/{size}',
		'auth' => 'http',
		'mime' => 'application/atom+xml',
	),
	'getMediaResource' =>    array (
		'uri_template' => 'edit-media/{collection_ascii_id}/{serial_number}/media/{size}',
		'auth' => 'http',
		'mime' => 'application/atom+xml',
	),
	'listCollectionEntries' =>    array (
		'uri_template' => 'edit/{collection_ascii_id}',
		'auth' => 'http',
		'method' => 'get',
		'mime' => 'application/atom+xml',
	),
	'createCollectionEntry' =>    array (
		'uri_template' => 'edit/{collection_ascii_id}',
		'auth' => 'http',
		'method' => 'post',
		'mime' => 'application/atom+xml',
	),
	'listItemMedia' =>    array (
		'uri_template' => 'edit/{collection_ascii_id}/{serial_number}/media',
		//'auth' => 'http',
		'auth' => 'none',
		'method' => 'get',
		'mime' => 'application/atom+xml',
	),
	'createMediaFile' =>    array (
		'uri_template' => 'edit/{collection_ascii_id}/{serial_number}',
		'auth' => 'http',
		'method' => 'post',
		'mime' => 'application/atom+xml',
	),
	'getItemServiceDoc' => array (
		'uri_template' => 'service/{collection_ascii_id}/{serial_number}',
		'auth' => 'http',
		'mime' => 'application/atomsvc+xml',
	),
	'getCollectionServiceDoc' => array (
		'uri_template' => 'service/{collection_ascii_id}',
		'auth' => 'http',
		'mime' => 'application/atomsvc+xml',
	),
	'deleteMediaFile' =>    array (
		'uri_template' => array(
			'edit-media/{collection_ascii_id}/{serial_number}/media/{size}',
			'edit/{collection_ascii_id}/{serial_number}/media/{size}',
		),
		'auth' => 'http',
		'method' => 'delete',
		'mime' => 'application/atom+xml',
	),
);
