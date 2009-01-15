<?php

$user = 'pkeane';
$pass = 'itsprop8';
$coll = 'itsprop';
$base = '/mnt/home/pkeane/dase_backup_sets';
$archive = $base.'/'.$coll;
$dase_url = 'http://dev.laits.utexas.edu/itsprop/new';

$new_ascii = 'batchtest';

//create collection
print postFile($archive.'/collection/entry.atom',$dase_url.'/collections',$user,$pass,$new_ascii);

//create item types 
foreach (new DirectoryIterator($archive.'/item_types') as $file) {
	if (!$file->isDot()) {
		print postFile($file->getPathname(),$dase_url.'/collection/'.$new_ascii.'/item_types',$user,$pass);
		print "\n";
	}
}

//create attributes
foreach (new DirectoryIterator($archive.'/attributes') as $file) {
	if (!$file->isDot()) {
		print postFile($file->getPathname(),$dase_url.'/collection/'.$new_ascii.'/attributes',$user,$pass);
		print "\n";
	}
}


function postFile($file_path,$url,$user,$pass,$slug='') {
	$ch = curl_init();
	// set URL and other appropriate options
	curl_setopt($ch, CURLOPT_URL, $url);
	//return headers
	curl_setopt($ch, CURLOPT_HEADER,true);
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
	curl_close($ch);
	$status_code = array(); 
	preg_match('/\d\d\d/', $response, $status_code); 
	if (isset($status_code[0])) {
		return $status_code[0];
	}
}

