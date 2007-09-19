#!/usr/bin/php
<?php
$database = 'dase_prod';
include 'cli_setup.php';

$archive = new Dase_DB_Collection;
$archive->ascii_id = 'friesen_collection';
$archive->findOne();
print($archive->xmlDump());
