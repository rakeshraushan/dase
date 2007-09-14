<?php

$database = 'dase_dev';
include 'cli_setup.php';

$c = Dase_DB_Collection::get('bsls_collection');
foreach ($c->getItems() as $it) {
	print $it->getAdminMetadata('admin_checksum') . "\n";;
}
