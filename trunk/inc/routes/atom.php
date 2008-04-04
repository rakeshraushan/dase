<?php

$routes['atom'] = array (
	//mostly cribbed from wordpress
	'get_post' =>    array (
		'uri_template' => 'app/collection/{collection_ascii_id}/post',
		'auth' => 'none',
	),
	'get_categories_xml' =>    array (
		'uri_template' => 'app/collection/{collection_ascii_id}/categories',
		'auth' => 'none',
	),
	'get_posts' =>    array (
		'uri_template' => 'app/collection/{collection_ascii_id}/posts',
		'auth' => 'none',
	),
	'get_attachment' =>    array (
		'uri_template' => 'app/collection/{collection_ascii_id}/attachment',
		'auth' => 'none',
	),
	'get_file' =>    array (
		'uri_template' => 'app/collection/{collection_ascii_id}/attachment/file',
		'auth' => 'none',
	),
	'create_post' =>    array (
		'uri_template' => 'app/collection/{collection_ascii_id}/posts',
		'auth' => 'none',
		'method' => 'post',
	),
	'create_attachment' =>    array (
		'uri_template' => 'app/collection/{collection_ascii_id}/attachments',
		'auth' => 'none',
		'method' => 'post',
	),
	'put_post' =>    array (
		'uri_template' => 'app/collection/{collection_ascii_id}/post',
		'auth' => 'none',
		'method' => 'put',
	),
	'put_file' =>    array (
		'uri_template' => 'app/collection/{collection_ascii_id}/attachment/file',
		'auth' => 'none',
		'method' => 'put',
	),
	'put_attachment' =>    array (
		'uri_template' => 'app/collection/{collection_ascii_id}/attachment',
		'auth' => 'none',
		'method' => 'put',
	),
	'delete_post' =>    array (
		'uri_template' => 'app/collection/{collection_ascii_id}/post',
		'auth' => 'none',
		'method' => 'delete',
	),
	'delete_file' =>    array (
		'uri_template' => 'app/collection/{collection_ascii_id}/attachment/file',
		'auth' => 'none',
		'method' => 'delete',
	),
	'delete_attachment' =>    array (
		'uri_template' => 'app/collection/{collection_ascii_id}/attachment',
		'auth' => 'none',
		'method' => 'delete',
	),
);
