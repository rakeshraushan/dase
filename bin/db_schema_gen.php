#!/usr/bin/php
<?php
$database = 'dase_test';
include 'cli_setup.php';

print(Dase_DB::getSchemaXml());
