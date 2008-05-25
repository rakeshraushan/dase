<?php

$routes['collection'] = array (
	'listAll' =>    array (
		'uri_template' => array('collection/list','collections','home',''),
		'auth' => 'none',
	),
	//just the facts ma'am:
	'asAtom' =>    array (
		'uri_template' => 'collection/{collection_ascii_id}',
		'auth' => 'none',
		'format' => 'atom',
	),
	//includes items:
	'asAtomArchive' =>    array (
		'uri_template' => 'collection/{collection_ascii_id}/archive',
		//to prevent DOS, needs high authorization
		'auth' => 'none',
		'format' => 'atom',
	),
	//includes managers, attributes, item_types:
	'AsAtomFull' =>    array (
		'uri_template' => 'collections/{collection_ascii_id}/full',
		'auth' => 'none',
		'format' => 'atom',
	),
	'listAsAtom' =>    array (
		'uri_template' => array('collections',''),
		'auth' => 'none',
		'format' => 'atom'
	),
	'attributesAsAtom' =>    array (
		'uri_template' => 'atom/collection/{collection_ascii_id}/attributes/public',
		'auth' => 'none',
	),
	'attributesAsHtml' =>    array (
		'uri_template' => 'collection/{collection_ascii_id}/attributes/public',
		'auth' => 'none',
	),
	'adminAttributesAsHtml' =>    array (
		'uri_template' => 'collection/{collection_ascii_id}/attributes/admin',
		'auth' => 'none',
	),
	'itemsByTypeAsAtom' =>    array (
		'uri_template' => 'atom/collection/{collection_ascii_id}/item_type/{item_type_ascii_id}',
		'auth' => 'none',
	),
	'browse' =>    array (
		'uri_template' => 'collection/{collection_ascii_id}',
		'auth' => 'user',
	),
	'itemTalliesAsJson' =>    array (
		'uri_template' => 'json/item_tallies',
		'auth' => 'none',
	),
	'attributeTalliesAsJson' =>    array (
		'uri_template' => 'json/collection/{collection_ascii_id}/attribute_tallies',
		'auth' => 'user',
	),
	'adminAttributeTalliesAsJson' =>    array (
		'uri_template' => 'json/collection/{collection_ascii_id}/admin_attribute_tallies',
		'auth' => 'user',
	),
	'buildIndex' =>    array (
		'uri_template' => 'ollection/buildInde',
		'auth' => 'admin',
	),
	'attributesAsJson' =>    array (
		'uri_template' => 'json/collection/{collection_ascii_id}/attributes',
		'auth' => 'user',
	),
	'asJsonCollection' =>    array (
		'uri_template' => 'json/{collection_ascii_id}',
		'auth' => 'none',
	),
);

