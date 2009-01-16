<?php

$user = 'pkeane';
$pass = 'itspro8';
$coll = 'itsprop';
$base = '/mnt/home/pkeane/dase_backup_sets';
$archive = $base.'/'.$coll;
$dase_url = 'http://dev.laits.utexas.edu/itsprop/new';

$new_ascii = $coll;

//create collection
print postFile($archive.'/collection/entry.atom',$dase_url.'/collections',$user,$pass,$new_ascii);
print " collection\n";

//create item types 
foreach (new DirectoryIterator($archive.'/item_types') as $file) {
	if (!$file->isDot()) {
		print postFile($file->getPathname(),$dase_url.'/collection/'.$new_ascii.'/item_types',$user,$pass);
		print ' item_type: '.$file."  ";
		print "\n";
	}
}

//create item type relations 
foreach (new DirectoryIterator($archive.'/item_type_relations') as $file) {
	if (!$file->isDot()) {
		print postFile($file->getPathname(),$dase_url.'/collection/'.$new_ascii.'/item_type_relations',$user,$pass);
		print ' item_type_relation: '.$file."  ";
		print "\n";
	}
}

//create attributes
foreach (new DirectoryIterator($archive.'/attributes') as $file) {
	if (!$file->isDot()) {
		print postFile($file->getPathname(),$dase_url.'/collection/'.$new_ascii.'/attributes',$user,$pass);
		print ' attribute: '.$file."  ";
		print "\n";
	}
}

//create items
foreach (new DirectoryIterator($archive.'/items') as $file) {
	if (!$file->isDot()) {
		print postFile($file->getPathname(),$dase_url.'/collection/'.$new_ascii,$user,$pass);
		print ' item: '.$file."  ";
		print "\n";
	}
}


function postFile($file_path,$url,$user,$pass,$slug='') {
	$ch = curl_init();
	// set URL and other appropriate options
	curl_setopt($ch, CURLOPT_URL, $url);
	//return headers
	//curl_setopt($ch, CURLOPT_HEADER,true);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
	curl_setopt($ch, CURLOPT_POSTFIELDS,@$file_path);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION,true);
	curl_setopt($ch, CURLOPT_USERPWD,$user.':'.$pass);
	$headers  = array(
		"Slug: $slug",
		"Content-type: application/atom+xml"
	);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$response = curl_exec($ch);
	//print $response."\n";
	$info = curl_getinfo($ch);
	curl_close($ch);
	return $info['http_code'];
}

