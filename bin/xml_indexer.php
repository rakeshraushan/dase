<?php

include 'cli_setup.php';

$cx = new Dase_Xml_Collection('vrc_collection');

print "created " . $cx->createIndex() . " bytes\n";
