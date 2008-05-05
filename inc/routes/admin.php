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
	'getAclAsJson' =>    array (
		'uri_template' => 'acl',
		'auth' => 'superuser',
		'mime' => 'application/json',
	),
);
