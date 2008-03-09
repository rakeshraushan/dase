<?php 

/************ configuration *********************/

$database = 'dase_prod';
$collection_ascii_id = 'atr253';
//$root = "http://harpo.laits.utexas.edu:6670"; //POW server!
$root = "http://webspace.utexas.edu";
$dir = "/atr253/images/bingham_blanton";
//$dir = "/keanepj/www/peter_keane/another_kind_of_blue";


/******************************************/
$i = 0;
include 'cli_setup.php';
$collection = Dase_DB_Collection::get($collection_ascii_id);
$rel_links = array();
foreach(file($root . $dir) as $line) {
//	print "line $line\n";
	$matches = array();
	//if (preg_match('/href="([^>]*)">/i',$line,$matches)) {
	if (preg_match('/href=([^>]*)>/i',$line,$matches)) {
		$i++;
		print "working on $i\n";
		if (strpos($matches[1],$dir)) {
		print "processing " .  $root . $matches[1] . "\n";
		processUrl($root . $matches[1],$collection);
		} else {
		//print "processing " .  $root . $dir . $matches[1] . "\n";
		//processUrl($root . $dir . $matches[1],$collection);
		print "processing " .  $root . $matches[1] . "\n";
		processUrl($root . $matches[1],$collection);
		}
	}
}

function processUrl($url,$collection) {
	$basename = basename($url);
	$headers = get_headers($url);
	foreach ($headers as $hdr) {
print "$hdr\n";
		if (preg_match('/content-type/i',$hdr)) {
			foreach(array('image','audio','video','pdf') as $format) {
				if (strpos($hdr,$format)) {
					$token = time();
					$tmp_filename = "/tmp/$token$basename";
					file_put_contents($tmp_filename,file_get_contents($url));
					try {
						$u = new Dase_Upload(Dase_File::newFile($tmp_filename),$collection);
						print $u->createItem();
						$u->setMetadata('scratch_pad','bingham_blanton');
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
