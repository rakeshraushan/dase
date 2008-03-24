<?php 

/************ configuration *********************/

$database = 'dase_prod';
#$collection_ascii_id = 'ut_collection';
#$REPOS = "/mnt/dar/fa/utcp/";
$collection_ascii_id = 'bsls';
$REPOS = "/mnt/projects/bsls/DASE weekly upload/";

/******************************************/


include 'cli_setup.php';
$collection = Dase_DBO_Collection::get($collection_ascii_id);

$dir = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($REPOS));
foreach ($dir as $file) {
	$matches = array();
	if (false === strpos($file->getPathname(),'/.') && $file->isFile()
		) {
			try {
				$u = new Dase_Upload(Dase_File::newFile($file->getPathname()),$collection);
//				$u->checkForMultiTiff();
				print $u->createItem();
				print $u->ingest();
				print $u->setTitle();
				//$u->setMetadata('scratch_pad','test');
				print $u->buildSearchIndex();
			} catch(Exception $e) {
				print $e->getMessage() . "\n";
			}
		}
}
