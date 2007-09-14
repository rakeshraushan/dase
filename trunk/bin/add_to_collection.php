<?php 

/************ configuration *********************/

$database = 'dase_prod';
$collection_ascii_id = 'ut_collection';
$REPOS = "/mnt/dar/fa/utcp/";

/******************************************/


include 'cli_setup.php';
$collection = Dase_DB_Collection::get($collection_ascii_id);

$dir = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($REPOS));
foreach ($dir as $file) {
	$matches = array();
	if (false === strpos($file->getPathname(),'/.') && $file->isFile()
		) {
			try {
				print $u = new Dase_Upload(Dase_File::newFile($file->getPathname()),$collection);
				print $u->createItem();
				print $u->ingest();
				print $u->setTitle();
				print $u->buildSearchIndex();
			} catch(Exception $e) {
				print $e->getMessage() . "\n";
			}
		}
}
