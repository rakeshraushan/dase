<?php

$routes['test'] = array (
	'first' => array (
		'uri_template' => array('test/first','test'),
		'auth' => 'superuser',
	),
	'search' => array (
		'uri_template' => 'test/search',
		'auth' => 'superuser',
	),
	'fail' => array (
		'uri_template' => 'test/fail',
		'auth' => 'superuser',
	),
	'testlist' => array (
		'uri_template' => 'test/list',
		'auth' => 'superuser',
	),
);
