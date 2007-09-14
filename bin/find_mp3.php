<?php

include 'cli_setup.php';



//$dir = "/mnt/projects/dase_scanning/mooretj_collection/uploaded_to_be_color_corrected/color_corrected/";
//$dir = "/mnt/www-data/dase/media/kerkhoff_collection/raw/";
//$dir = "/mnt/www-data/dase/media/canzoni_collection/mp3/";
$dir = "/mnt/projects/bsls/DASE weekly upload/";
$di = new DirectoryIterator($dir);
foreach($di as $line) {
	$matches = array();
	//if ((preg_match('/(.*)\.tif/i',$line,$matches) || preg_match('/(.*)\.jpg/i',$line,$matches)) &&  (false === strpos($line,'._'))) {
	if (false === strpos($line,'._') && !$line->isDot() && strpos($line,'.mp3')) {
		try {
		$img = Dase_File::newFile($dir . $line);
		print_r($img->getMetadata());
		} catch(Exception $e) {
			//
		}
		//print $line . "\n";
		//exec("file -i -b $line");
		//print $line . " " . $matches[1] . "\n";
	}
}
