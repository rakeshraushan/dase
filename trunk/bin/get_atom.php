#!/usr/bin/php
<?php
$database = 'dase_prod';
include 'cli_setup.php';
define(APP_HTTP_ROOT,'/mnt/projects');

$coll = Dase_DB_Collection::get('vrc_collection');
print($coll->getAtom());
