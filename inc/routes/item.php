<?php
//MAKE SURE THAT items are NOT created w/ serial numbers that could be URL filters ('attribute', etc.)
$routes['item'] = array( 
	'asAtom' =>    array (
		'uri_template' => 'atom/collection/{collection_ascii_id}/{serial_number}',
		'auth' => 'none',
		'mime' => 'application/atom+xml',
	),
	'display' =>    array (
		'uri_template' => 'collection/{collection_ascii_id}/{serial_number}',
		'auth' => 'user',
	),
	'editForm' =>    array (
		'uri_template' => 'user/{eid}/collection/{collection_ascii_id}/{serial_number}/form',
		'auth' => 'eid',
	),
);

