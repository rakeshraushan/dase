<?php

$routes['admin'] = array (
	'smarty' =>    array (
		'uri_template' => 'smarty',
		//'auth' => 'superuser',
		'auth' => 'none',
	),
	'exec' =>    array (
		'uri_template' => 'exec',
		'auth' => 'superuser',
	),
	'monitor' => array (
		'uri_template' => 'monitor',
		'auth' => 'superuser',
	),
	'calendar' => array (
		'uri_template' => 'calendar',
		'auth' => 'superuser',
	),
	'phpinfo' =>    array (
		'uri_template' => 'phpinfo',
		'auth' => 'superuser',
	),
	'getAcl' =>    array (
		'uri_template' => 'acl',
		'auth' => 'http',
	),
	'getMediaSourceList' =>    array (
		'uri_template' => 'sources',
		'auth' => 'http',
	),
	'testMimeParser' =>    array (
		'uri_template' => 'mime',
		'auth' => 'none',
	),
);
