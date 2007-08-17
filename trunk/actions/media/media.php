<?php  
//ini_set('display_errors',1);
//error_reporting(E_ALL);
//0 means all access, 1 means EID only, 2 means collection owner
$media_conf['400']['access_flag'] = 0;
$media_conf['400']['mime_type'] = 'image/jpeg';
;
$media_conf['aiff']['access_flag'] = 2;
$media_conf['aiff']['mime_type'] = 'audio/x-aiff';
;
$media_conf['css']['access_flag'] = 0;
$media_conf['css']['mime_type'] = 'text/css';
;
$media_conf['deleted']['access_flag'] = 2;
$media_conf['deleted']['mime_type'] = 'application/octet-stream';
;
$media_conf['doc']['access_flag'] = 1;
$media_conf['doc']['mime_type'] = 'application/msword';
;
$media_conf['full']['access_flag'] = 1;
$media_conf['full']['mime_type'] = 'image/jpeg';
;
$media_conf['gif']['access_flag'] = 1;
$media_conf['gif']['mime_type'] = 'image/gif';
;
$media_conf['html']['access_flag'] = 0;
$media_conf['html']['mime_type'] = 'text/html';
;
$media_conf['jpeg']['access_flag'] = 1;
$media_conf['jpeg']['mime_type'] = 'image/jpeg';
;
$media_conf['large']['access_flag'] = 1;
$media_conf['large']['mime_type'] = 'image/jpeg';
;
$media_conf['medium']['access_flag'] = 1;
$media_conf['medium']['mime_type'] = 'image/jpeg';
;
$media_conf['midi']['access_flag'] = 1;
$media_conf['midi']['mime_type'] = 'application/octet-stream';
;
$media_conf['mp3']['access_flag'] = 2;
$media_conf['mp3']['mime_type'] = 'audio/mpeg';
;
$media_conf['pdf']['access_flag'] = 1;
$media_conf['pdf']['mime_type'] = 'application/pdf';
;
$media_conf['png']['access_flag'] = 1;
$media_conf['png']['mime_type'] = 'image/png';
;
$media_conf['quicktime']['access_flag'] = 2;
$media_conf['quicktime']['mime_type'] = 'video/quicktime';
;
$media_conf['quicktime_stream']['access_flag'] = 1;
$media_conf['quicktime_stream']['mime_type'] = 'video/quicktime';
;
$media_conf['raw']['access_flag'] = 2;
$media_conf['raw']['mime_type'] = 'application/octet-stream';
;
$media_conf['small']['access_flag'] = 1;
$media_conf['small']['mime_type'] = 'image/jpeg';
;
$media_conf['text']['access_flag'] = 0;
$media_conf['text']['mime_type'] = 'text/plain';
;
$media_conf['thumbnail']['access_flag'] = 0;
$media_conf['thumbnail']['mime_type'] = 'image/jpeg';
;
$media_conf['thumbnails']['access_flag'] = 0;
$media_conf['thumbnails']['mime_type'] = 'image/jpeg';
;
$media_conf['tiff']['access_flag'] = 1;
$media_conf['tiff']['mime_type'] = 'image/tiff';
;
$media_conf['viewitem']['access_flag'] = 0;
$media_conf['viewitem']['mime_type'] = 'image/jpeg';

$media_conf['wav']['access_flag'] = 2;
$media_conf['wav']['mime_type'] = 'audio/x-wav';
;
$media_conf['xml']['access_flag'] = 0;
$media_conf['xml']['mime_type'] = 'application/xml';
;
$media_conf['xslt']['access_flag'] = 0;
$media_conf['xslt']['mime_type'] = 'application/xslt+xml';

//should be generated into acl from db
$public_access_collections = array(
	'germans_from_russia_collection',
	'biodoc_collection',
	'bsls_collection',
	'asl_collection',
	'texpol_cms_collection',
	'efossils_collection',
	'eskeletons_collection',
	'american_politics_collection'
);

$collection = $params['collection']; 
$size = $params['size']; 
$filename = $params['filename']; 
$download = Dase::filterGet('force_download');

if (stristr($filename,'..')) { //prevent hacker from going up dir tree
	exit;
}

//public collection, so give 'em what they want
if (in_array($collection,$public_access_collections)) {
	serveFile($collection,$size,$filename,$download,$media_conf);
	exit;
}

//unrestricted filetype, so give 'em what they want
if (!$media_conf[$size]['access_flag']) {
	serveFile($collection,$size,$filename,$download,$media_conf);
	exit;
}

if (!extension_loaded("eid")) {
	dl("eid.so");
	if (!extension_loaded("eid")) {
		die('no go eid module');
	}
}

$ut_user = eid_decode(); 
if (EID_ERR_OK != $ut_user->status) {
	unset($ut_user);
}

if ($ut_user == NULL) {
	unset($download);
	$size = 'thumbnail';
	$filename = getThumb($filename);
	serveFile($collection,$size,$filename,$download,$media_conf);
	exit;
}	

if ($ut_user) {
	if (2 == $media_conf[$size]['access_flag']) {
		require_once '../inc/media_acl.php';
		$eid = $ut_user->eid;
		if (!$acl[$collection][$eid]) {
			unset($download);
			$size = 'thumbnail';
			$filename = getThumb($filename);
		}
	}
	serveFile($collection,$size,$filename,$download,$media_conf);
	exit;
}

function getThumb($filename) {
	$ext = array('_100.jpg','_400.jpg','_640.jpg','_800.jpg','_1024.jpg','_4800.jpg');
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

function serveFile($collection,$size,$filename,$download,$media_conf) {
	$media_root="/mnt/www-data/dase/media";
	//when I get around to it, I need to rename image directories to match "size" attribute
	//of media_file exactly (AND strip off directory name part of filename)
	//but it'll have to wait 'til I redo collection builder
	if ('thumbnail' == $size) {
		$size = 'thumbnails';
	}
	if ('viewitem' == $size) {
		$size = '400';
	}
	$path = "$media_root/$collection/$size/$filename";

	if ((!file_exists($path)) || (!$filename) || (!$size)) {
		header('Content-Type: image/jpeg');
		readfile('../images/unavail.jpg');
		exit;
	}
	$content_type = $media_conf[$size]['mime_type'];
	if (!$content_type) {
		$content_type = 'application/octet-stream';
	}
	header('Last-Modified: '.date('r',filemtime($path)));
	header('Content-Length: '.filesize($path));
	header('Content-Type: '.$content_type);
	if ($download) {
		header("Content-Disposition: attachment; filename=$filename");
	} else {
		header("Content-Disposition: inline; filename=$filename");
	}
	readfile($path);
	return;
}

/*
 * from php.net:
 When using PHP to output an image, it won't be cached by the client so if you don't want them to download the image each time they reload the page, you will need to emulate part of the HTTP protocol.
 Here's how:
<?php
Test image.
$fn = '/test/foo.png';
// Getting headers sent by the client.
$headers = apache_request_headers();
// Checking if the client is validating his cache and if it is current.
if (isset($headers['If-Modified-Since']) && (strtotime($headers['If-Modified-Since']) == filemtime($fn))) {
	// Client's cache IS current, so we just respond '304 Not Modified'.
	header('Last-Modified: '.gmdate('D, d M Y H:i:s', filemtime($fn)).' GMT', true, 304);
} else {
	// Image not cached or cache outdated, we respond '200 OK' and output the image.
	header('Last-Modified: '.gmdate('D, d M Y H:i:s', filemtime($fn)).' GMT', true, 200);
	header('Content-Length: '.filesize($fn));
	header('Content-Type: image/png');
	print file_get_contents($fn);
}
?>
	That way foo.png will be properly cached by the client and you'll save bandwith. :)
 */


/*
class Dase_MediaHandler 
{
	public static function index() {
		$params = func_get_args();
		$collection = $params[0];
		$size = $params[1];
		$filename = $params[2];
		$download = Dase::filterGet('force_download');
		if (stristr($filename,'..')) { //prevent hacker from going up dir tree
			exit;
		}
		$path = MEDIA_ROOT ."/$collection/$size/$filename";
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
 */
