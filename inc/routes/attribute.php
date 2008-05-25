<?php

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
