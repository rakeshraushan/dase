#!/usr/bin/php
<?php
$database = 'dase_prod';
include 'cli_setup.php';
define('APP_HTTP_ROOT','http://quickdraw.laits.utexas.edu/dase');
define('APP_ROOT','http://quickdraw.laits.utexas.edu/dase');

$coll = Dase_DB_Collection::get('texpol_image_collection');
print($coll->getAtom());
