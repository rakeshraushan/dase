#!/usr/bin/php
<?php
$database = 'dase_prod';
include 'cli_setup.php';

print(Dase_DB::getSchemaXml());
