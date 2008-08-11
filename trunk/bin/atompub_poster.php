<?php

//this is a standard php-based image POSTer suitable for
//use with an AtomPub implementation
exit;
ini_set('include_path','/usr/local/lib/php');

function __autoload($class_name) {
	$include_path_tokens = explode(':', get_include_path());
	foreach($include_path_tokens as $inc_dir){
		$class_file = $inc_dir . '/' . preg_replace('/_/','/',$class_name) . '.php';
		if(file_exists($class_file)){
			require_once $class_file;
			return;
		}
	}  
}

$IMAGE_DIR = '/mnt/dar/favrc/Archivision/BASE_TIFFS/';

$user = 'pkeane';
$pwd = '07253648';

$dir = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($IMAGE_DIR));
foreach ($dir as $file) {
	$matches = array();
	if (strpos($file->getPathname(),'.tif')) {
		$f = basename($file->getFilename(),'.tif');
		$url = "http://$user:$pwd@quickdraw.laits.utexas.edu/dase1/search.atom?archivision.filename=$f";
		$feed = Dase_Atom_Feed::retrieve($url);
		$media_coll_url = $feed->entries[0]->mediaCollectionLink;
		$mime = mime_content_type($file->getPathname());
		/** wonderful memory leak
		$data = file_get_contents($file->getPathname());
		$auth = base64_encode("$user:$pwd");
		$context_options = array (
			'http' => array (
				'method' => 'POST',
				'header'=> "Content-type: $mime\r\n"
				. "Content-Length: " . strlen($data) . "\r\n"
				. "Authorization: Basic $auth\r\n",
					'content' => $data
				)
			);
		$context = stream_context_create($context_options);
		file_get_contents($media_coll_url.'?auth=http',0,$context);
		 */
		$filepath = $file->getPathname();
		print exec("/usr/bin/curl --data-binary @$filepath -X post -H 'Content-type: $mime' -u pkeane:skeletonkey $media_coll_url?auth=http");
		print "uploaded $media_coll_url\n";
	}
}
