<?php 

/************ configuration *********************/

$database = 'dase_prod';
$collection_ascii_id = 'medieval';
$repo = $collection_ascii_id.'_collection';
$REPOS = "/mnt/projects/dase_scanning/$repo/uploaded_to_be_color_corrected/color_corrected";
$ARCHIVE = "/mnt/projects/dase_scanning/$repo/to_be_archived/color_corrected";

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
				$u->checkForMultiTiff();
				if ($u->retrieveItem()) {
					print $u->deleteItemMedia();
					print $u->deleteItemAdminMetadata();
					print $u->ingest();
					print $u->buildSearchIndex();
					print $u->moveFileTo($ARCHIVE);
				} else {
					print "NO GO $file\n";
				}
			} catch(Exception $e) {
				print $e->getMessage() . "\n";

			}
		}
}
