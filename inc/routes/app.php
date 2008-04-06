<?php

$routes['app'] = array (
	'getMediaLinkEntry' =>    array (
		'uri_template' => 'edit/{collection_ascii_id}/{serial_number}/{size}',
		'auth' => 'none',
		'mime' => 'application/atom+xml',
	),
	'getMediaResource' =>    array (
		'uri_template' => 'edit-media/{collection_ascii_id}/{serial_number}/{size}',
		'auth' => 'none',
		'mime' => 'application/atom+xml',
	),
	'listCollectionEntries' =>    array (
		'uri_template' => 'edit/{collection_ascii_id}',
		//need authentication!!!!!!!
		'auth' => 'none',
		'method' => 'get',
		'mime' => 'application/atom+xml',
	),
	'createCollectionEntry' =>    array (
		'uri_template' => 'edit/{collection_ascii_id}',
		//need authentication!!!!!!!
		'auth' => 'none',
		'method' => 'post',
		'mime' => 'application/atom+xml',
	),
	'listItemMedia' =>    array (
		'uri_template' => 'edit/{collection_ascii_id}/{serial_number}',
		//need authentication!!!!!!!
		'auth' => 'none',
		'method' => 'get',
		'mime' => 'application/atom+xml',
	),
	'createMediaFile' =>    array (
		'uri_template' => 'edit/{collection_ascii_id}/{serial_number}',
		//need authentication!!!!!!!
		'auth' => 'none',
		'method' => 'post',
		'mime' => 'application/atom+xml',
	),
	'getItemServiceDoc' => array (
		'uri_template' => 'service/{collection_ascii_id}/{serial_number}',
		'auth' => 'none',
		'mime' => 'application/atomsvc+xml',
	),
	'getCollectionServiceDoc' => array (
		'uri_template' => 'service/{collection_ascii_id}',
		'auth' => 'none',
		'mime' => 'application/atomsvc+xml',
	),
	'deleteMediaFile' =>    array (
		'uri_template' => array(
			'edit-media/{collection_ascii_id}/{serial_number}/{size}',
			'edit/{collection_ascii_id}/{serial_number}/{size}',
		),
		//need authentication!!!!!!!
		'auth' => 'none',
		'method' => 'delete',
		'mime' => 'application/atom+xml',
	),
);
