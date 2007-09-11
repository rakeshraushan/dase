<?php 
$root = "http://webspace.utexas.edu";
$dir = "/keanepj/www/peter_keane/another_kind_of_blue";
$rel_links = array();
foreach(file($root . $dir) as $line) {
	$matches = array();
	if (preg_match('/href=([^>]*)>/i',$line,$matches)) {
		processUrl($root . $matches[1]);
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
