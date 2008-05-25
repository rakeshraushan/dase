<?php  
/*
 * Copyright 2008 The University of Texas at Austin
 *
 * This file is part of DASe.
 * 
 * DASe is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * DASe is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with DASe.  If not, see <http://www.gnu.org/licenses/>.
 */ 

include 'sources.php';
include 'media_config.php';

//choose authentication to set eid:
//include 'uteid_authentication.php';
include 'dase_cookie_authentication.php';

//need to filter these
$collection = $_GET['collection'];
$size = $_GET['size'];
$filename = $_GET['filename'];
$download = $_GET['force_download'];
//ini_set('display_errors',1);
//error_reporting(E_ALL);

//should be generated into acl from db
$public_access_collections = array(
	'germans_from_russia',
	'biodoc',
	'bsls',
	'asl',
	'texpol_cms',
	'efossils',
	'eskeletons',
	'american_politics'
);

if (stristr($filename,'..')) { //prevent hacker from going up dir tree
	exit;
}

//public collection, so give 'em what they want
if (in_array($collection,$public_access_collections)) {
	serveFile($collection,$size,$filename,$download,$media_conf,$sources);
	exit;
}

//unrestricted filetype, so give 'em what they want
if (!$media_conf[$size]['access_flag']) {
	serveFile($collection,$size,$filename,$download,$media_conf,$sources);
	exit;
}


if (!$eid) {
	unset($download);
	$size = 'thumbnail';
	$filename = getThumb($filename);
	serveFile($collection,$size,$filename,$download,$media_conf,$sources);
	exit;
} else {	
	if (2 == $media_conf[$size]['access_flag']) {
		include 'acl.php';
		if (!$acl['collections'][$collection][$eid]) {
			unset($download);
			$size = 'thumbnail';
			$filename = getThumb($filename);
		}
	}
	serveFile($collection,$size,$filename,$download,$media_conf,$sources);
	exit;
}

function getThumb($filename)
{
	$ext = array('_100.jpg','_400.jpg','_640.jpg','_800.jpg','_1024.jpg','_4800.jpg','.pdf');
	$filename = str_replace($ext,'_100.jpg',$filename);
	//really these others should be determined by 'size' param
	if (strpos($filename,'.mp3')) {
		$filename = 'audio.jpg';
	}
	if (strpos($filename,'.mov')) {
		$filename = 'quicktime.jpg';
	}
	return $filename;
}

function serveFile($collection,$size,$filename,$download,$media_conf,$sources)
{
	$media_root = $sources[$collection];
	if (!$media_root) {
		$media_root = '/mnt/www-data/dase/media/'.$collection;
	}

	$content_type = $media_conf[$size]['mime_type'];
	if (!$content_type) {
		$content_type = 'application/octet-stream';
	}

	//when I get around to it, I need to rename image directories to match "size" attribute
	//of media_file exactly (AND strip off directory name part of filename)
	//but it'll have to wait 'til I redo collection builder
	if ('thumbnail' == $size) {
		$size = 'thumbnails';
	}
	if ('viewitem' == $size) {
		$size = '400';
	}
	//$path = "$media_root/{$collection}_collection/$size/$filename";
	$path = "$media_root/$size/$filename";
	if (!file_exists($path)) {
		$path = "{$media_root}_collection/$size/$filename";
	}

	if ((!file_exists($path)) || (!$filename) || (!$size)) {
		header('Content-Type: image/jpeg');
		readfile('../images/unavail.jpg');
		exit;
	}
	//from php.net
	$headers = apache_request_headers();
	// Checking if the client is validating its cache and if it is current.
	if (isset($headers['If-Modified-Since']) && (strtotime($headers['If-Modified-Since']) == filemtime($path))) {
		// Client's cache IS current, so we just respond '304 Not Modified'.
		header('Last-Modified: '.gmdate('D, d M Y H:i:s', filemtime($path)).' GMT', true, 304);
	} else {
		// Image not cached or cache outdated, we respond '200 OK' and output the image.
		header('Last-Modified: '.gmdate('D, d M Y H:i:s', filemtime($path)).' GMT', true, 200);
		header('Content-Length: '.filesize($path));
		header('Content-Type: '.$content_type);
		if ($download) {
			header("Content-Disposition: attachment; filename=$filename");
		} else {
			header("Content-Disposition: inline; filename=$filename");
		}
		print file_get_contents($path);
	}
}

