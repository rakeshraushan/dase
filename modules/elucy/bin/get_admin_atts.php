#!/usr/bin/php
<?php
$database = 'dase_prod';
include 'cli_setup.php';
define('APP_ROOT', 'http://quickdraw.laits.utexas.edu/dase');
define('MEDIA_ROOT', '/mnt/www-data/dase/media');

$att = new Dase_DB_Attribute;
$att->collection_id = 0;
foreach ($att->findAll() as $a) {
	print $a['ascii_id'] . "\n";
}
