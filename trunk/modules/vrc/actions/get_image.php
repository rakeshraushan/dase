<?php
$images = array();

$IMAGE_REPOS = "/mnt/dar/favrc/for-dase";
if (!file_exists($IMAGE_REPOS)) {
	die ("cannot find $IMAGE_REPOS");
}
$dir = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($IMAGE_REPOS));
$images = array();
foreach ($dir as $file) {
	if (!strpos($file,'/.')) {
		if (strpos($file,'.jpg') || strpos($file,'.tif')) {
			$images[basename($file)]= $file->getPathname();
		}
	}
}

if ($params['filename']) {
	$key = $params['filename'];
	if (($images[$key]) && file_exists($images[$key])) {
		header('Content-Type: image/tiff');
		readfile($images[$key]);
		exit;
	}
}

