<?php

$q = trim($argv[1]);
include 'cli_setup.php';

$cx = new Dase_Xml_Collection('vrc_collection');
$cx->prepareIndex();

//print $cx->getItemsBySerialNumbers($cx->find($q)) . "\n";
print $cx->find($q) . "\n";
