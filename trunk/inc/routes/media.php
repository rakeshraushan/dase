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
);

