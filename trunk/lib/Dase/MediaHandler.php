<?php

class Dase_MediaHandler 
{
	public static function index() {
		$params = func_get_args();
		$collection_name = $params[0];
		$size = $params[1];
		$filename = $params[2];
		$download = Dase_Utils::filterGet('force_download');
		if (stristr($filename,'..')) { //prevent hacker from going up dir tree
			exit;
		}
		$path = MEDIA_ROOT ."/$collection_name/$size/$filename";
		if ((!file_exists($path)) || (!$filename) || (!$size)) {
			header('Content-Type: image/jpeg');
			readfile(DASE_PATH .'/images/unavail.jpg');
			exit;
		}
		$image_sizes = array('thumbnail','viewitem','small','medium','large','full');
		if (in_array($size,$image_sizes)) {
			//say we could have png icons, right??
			$content_type = 'image/jpeg';
		}
		if (('quicktime' == $size) || ('quicktime_streaming' == $size)) {
			$content_type = 'video/quicktime';
		}
		if ('xml' == $size) {
			$content_type = 'application/xml';	
		}
		if ('xslt' == $size) {
			$content_type = 'application/xslt+xml';	
		}
		if ('css' == $size) {
			$content_type = 'text/css';	
		}
		if ('text' == $size) {
			$content_type = 'text/plain';	
		}
		if ('html' == $size) {
			$content_type = 'text/html';	
		}
		if ('png' == $size) {
			$content_type = 'image/png';
		}
		if ('gif' == $size) {
			$content_type = 'image/gif';
		}
		if ('mp3' == $size) {
			$content_type = 'audio/mpeg';
		}
		if ('pdf' == $size) {
			$content_type = 'application/pdf';
		}
		if (!$content_type) {
			$content_type = 'application/octet-stream';
		}
		//this breaks everything:
		//header('Last-Modified: '.date(r,filemtime($path)));
		header('Content-Length: '.filesize($path));
		header('Content-Type: '.$content_type);
		if ($download) {
			header("Content-Disposition: attachment; filename=$filename");
		} else {
			header("Content-Disposition: inline; filename=$filename");
		}
		readfile($path);
		exit;
	}
}
