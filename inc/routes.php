<?php

//todo: create test suite for route dispatching
include 'routes/search.php';
include 'routes/item.php';
include 'routes/collection.php';
include 'routes/atompub.php';
include 'routes/user.php';
include 'routes/tag.php';
include 'routes/media.php';
include 'routes/admin.php';
include 'routes/collectionbuilder.php';
include 'routes/test.php';


$routes['attribute'] = array( 
	'attributeValuesAsHtml' =>    array (
		'uri_template' => 'collection/{collection_ascii_id}/attribute/{attribute_ascii_id}',
		'auth' => 'none',
	),
	'attributeListAsAtom' =>    array (
		'uri_template' => 'atom/attributes',
		'auth' => 'none',
	),
);

$routes['widget'] = array (
	'init' =>    array (
		'uri_template' => 'scripts/widgets.js',
		'auth' => 'none',
		'mime' => 'application/x-javascript',
	),
);

$routes['css'] = array(
	'init' =>    array (
		'uri_template' => 'css/dynamic.css',
		'auth' => 'none',
		'mime' => 'text/css',
	),
);

