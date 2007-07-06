#!/usr/bin/php
<?php
$database = 'dase_test';
include 'cli_setup.php';

$archive = new Dase_DB_Collection;
$archive->ascii_id = 'efossils_collection';
$archive->find(1);
print($archive->xmlDump());
