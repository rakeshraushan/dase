<?php

$routes['collectionbuilder'] = array(
	'index' =>    array (
		'uri_template' => 'cb/{eid}/{collection_ascii_id}',
		'auth' => 'admin',
	),
	'settings' =>    array (
		'uri_template' => 'cb/{eid}/{collection_ascii_id}/settings',
		'auth' => 'admin',
	),
	'managers' =>    array (
		'uri_template' => 'cb/{eid}/{collection_ascii_id}/managers',
		'auth' => 'admin',
	),
	'item_types' =>    array (
		'uri_template' => 'cb/{eid}/{collection_ascii_id}/item_types',
		'auth' => 'admin',
	),
	'attributes' =>    array (
		'uri_template' => 'cb/{eid}/{collection_ascii_id}/attributes',
		'auth' => 'admin',
	),
	'dataAsJson' =>    array (
		'uri_template' => 'json/collection/{collection_ascii_id}/data/{select}',
		'auth' => 'read',
	),
	'setAttributeSortOrder' =>    array (
		'uri_template' => 'cb/{eid/collection_ascii_id/attribute_ascii_id}/XXX/attribute/XXX/sort_order',
		'auth' => 'admin',
		'method' => 'put',
	),
	'uploadForm' =>    array (
		'uri_template' => 'cb/{eid}/{collection_ascii_id}/upload',
		'auth' => 'admin',
	),
	'checkAtom' =>    array (
		'uri_template' => 'cb/{eid}/{collection_ascii_id}/check_atom',
		'auth' => 'admin',
		'method' => 'post',
	),
);

