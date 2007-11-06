<?php 

/************ configuration *********************/

$database = 'dase_prod';
$collection_ascii_id = 'rm7233_collection';
//$repo = $collection_ascii_id;
$repo = mansfield;

/******************************************/

$REPOS = "/mnt/projects/dase_scanning/$repo";

include 'cli_setup.php';
$collection = Dase_DB_Collection::get($collection_ascii_id);

$logfile = $REPOS . '/dase_upload_' . date('Ymd') . '.log';

checkForAdminDirs($REPOS);
processDir($REPOS,$collection,$logfile);

function processDir($REPOS,$collection,$logfile) {
	$dir = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($REPOS));
	foreach ($dir as $file) {
		$matches = array();
		if (
			false === strpos($file->getPathname(),'/.') && 
			false === strpos($file->getPathname(),'to_be_color_corrected') &&
			false === strpos($file->getPathname(),'to_be_archived') &&
			false === strpos($file->getPathname(),'to_be_deleted') &&
			false === strpos($file->getPathname(),'bad_file_format') &&
			false === strpos($file->getPathname(),'dase_upload_') &&
			$file->isFile()
		) {
			try {
					$u = new Dase_Upload(Dase_File::newFile($file->getPathname()),$collection);
			//		$u->checkForMultiTiff();
					$ser_num = $u->createItem();
					$logdata = $u->ingest();
					$logdata .= $u->setTitle();
					$logdata .= $u->buildSearchIndex();
					$orig = $file->getPathname();
					$info = pathinfo($orig);
					$dest = $REPOS . '/uploaded_to_be_color_corrected/' . $ser_num . '.' .$info['extension'];
					copy($orig,$dest) or die("couldn't copy $old to $new: $php_errormsg");
					$logdata .= "\nCOPIED $orig to $dest\n\n"; 
					print "$logdata\n";
					file_put_contents($logfile,$logdata,FILE_APPEND);
					
			} catch(Exception $e) {
				print $e->getMessage() . "\n";
				file_put_contents($logfile,$e->getMessage(),FILE_APPEND);
			}
		}
	}
}

function checkForAdminDirs($REPOS) {
	if (!file_exists($REPOS)) {
		print "$REPOS does NOT exist!\n";
		exit;
	}
	$admin_dirs = array(
		'uploaded_to_be_color_corrected', 
		'uploaded_to_be_color_corrected/color_corrected', 
		'to_be_archived', 
		'to_be_archived/raw', 
		'to_be_archived/color_corrected', 
		'to_be_deleted', 
		'bad_file_format'
	);
	foreach ($admin_dirs as $dir) {
		if (!file_exists($REPOS . '/' . $dir)) {
			$new = $REPOS . '/' . $dir;
			print "$new does NOT exist!\n creating $new...";
			mkdir($new);
			chgrp($new, 'scanners');
			chmod($new, 02775);
			print "done!\n";
		} else {
			print "$REPOS/$dir exists.\n";
		}
	}
}
