<?php

$routes['media'] = array(
	'getMediaAttributes' => array (
		'uri_template' => 'media/attributes',
		'auth' => 'superuser',
	),
	'updateMediaAttribute' => array (
		'uri_template' => 'media/attribute/{id}',
		'auth' => 'superuser',
		'method' => 'post',
	),
	'get' => array (
		'uri_template' => 'media/{collection_ascii_id}/{size}/{filename}',
		'auth' => 'none', //auth will be taken care of in handler
	),
);

