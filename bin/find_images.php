<?php 

include 'cli_setup.php';

$dir = "/mnt/projects/dase_scanning/mooretj_collection/uploaded_to_be_color_corrected/color_corrected/";
//$dir = "/mnt/www-data/dase/media/kerkhoff_collection/raw/";
$di = new DirectoryIterator($dir);
foreach($di as $line) {
	$matches = array();
	//if ((preg_match('/(.*)\.tif/i',$line,$matches) || preg_match('/(.*)\.jpg/i',$line,$matches)) &&  (false === strpos($line,'._'))) {
	if (false === strpos($line,'._') && !$line->isDot()) {
		try {
		$u = new Dase_upload(Dase_File::newFile($dir . $line),'mooretj_collection',1);
		//print_r($u);
		} catch(Exception $e) {
			//
		}
		//print $line . "\n";
		//exec("file -i -b $line");
		//print $line . " " . $matches[1] . "\n";
	}
}

function processUrl($url) {
	$basename = basename($url);
	$headers = get_headers($url);
	foreach ($headers as $hdr) {
		if (preg_match('/content-type/i',$hdr)) {
			foreach(array('image','audio','video') as $format) {
				if (strpos($hdr,$format)) {
					$token = time();
					$tmp_filename = "/tmp/$token$basename";
					file_put_contents($tmp_filename,file_get_contents($url));
					$new_file = new Dase_File($tmp_filename);
					$new_file->ingest();
					//print filesize($tmp_filename);
					//$output = array();
					//exec("file -i -b $tmp_filename",$output);
					//print " " . $output[0] . "\n";
					unlink($tmp_filename);
				}
			}
		}
	}
}
