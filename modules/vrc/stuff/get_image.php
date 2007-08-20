<?php

$images = array();

include 'images.php';

if ($_GET['key']) {
	$key = $_GET['key'];
	if (($images[$key]) && file_exists($images[$key])) {
		header('Content-Type: image/tiff');
		readfile($images[$key]);
		exit;
	}
}

