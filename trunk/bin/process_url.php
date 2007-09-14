<?php 

/************ configuration *********************/

//$database = 'dase_prod';
$collection_ascii_id = 'test_collection';
$root = "http://webspace.utexas.edu";
$dir = "/keanepj/www/peter_keane/another_kind_of_blue";

/******************************************/

include 'cli_setup.php';
$collection = Dase_DB_Collection::get($collection_ascii_id);
$rel_links = array();
foreach(file($root . $dir) as $line) {
	$matches = array();
	if (preg_match('/href=([^>]*)>/i',$line,$matches)) {
		processUrl($root . $matches[1],$collection);
	}
}

function processUrl($url,$collection) {
	$basename = basename($url);
	$headers = get_headers($url);
	foreach ($headers as $hdr) {
		if (preg_match('/content-type/i',$hdr)) {
			foreach(array('image','audio','video') as $format) {
				if (strpos($hdr,$format)) {
					$token = time();
					$tmp_filename = "/tmp/$token$basename";
					file_put_contents($tmp_filename,file_get_contents($url));
					try {
						$u = new Dase_Upload(Dase_File::newFile($tmp_filename),$collection);
						print $u->createItem();
						print $u->ingest();
						print $u->buildSearchIndex();
					} catch(Exception $e) {
						print $e->getMessage() . "\n";
					}
					unlink($tmp_filename);
				}
			}
		}
	}
}
