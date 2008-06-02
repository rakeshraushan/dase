<?php

class Dase_Http_Response
{
	private $request;

	public function __construct($request)
	{
		$this->request =  $request;
	}

	public function render($content,$set_cache=true)
	{
		if ($set_cache) {
			$cache = Dase_Cache::get($this->request->getCacheId());
			$cache->setData($content);
		}
		header("Content-Type: ".$this->request->response_mime_type."; charset=utf-8");
		echo $content;
		exit;
	}

	public function serveFile($path,$mime_type,$download=false)
	{
		if (!file_exists($path)) {
			header('Content-Type: image/jpeg');
			readfile(DASE_PATH.'/www/images/unavail.jpg');
			exit;
		}
		$filename = basename($path);
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
			header('Content-Type: '.$mime_type);
			if ($download) {
				header("Content-Disposition: attachment; filename=$filename");
			} else {
				header("Content-Disposition: inline; filename=$filename");
			}
			print file_get_contents($path);
		}
		exit;
	}

	public function redirect($path='',$msg='',$code="303",$format='html')
	{
		//SHOULD use 303 (redirect after put,post,delete)
		//OR 307 -- no go -- look here
		$msg = urlencode($msg);
		//NOTE that this redirect may be innapropriate when
		//client expect something OTHER than html (e.g., json,text,xml)
		$redirect_path = trim(APP_ROOT,'/') . "/" . trim($path,'/').'?msg='.$msg.'&format='.$format;
		Dase_Log::info('redirecting to '.$redirect_path);
		header("Location:". $redirect_path,TRUE,$code);
		exit;
	}

	public function error($code,$msg='')
	{
		switch ($code) {
		case 400:
			header("HTTP/1.1 400 Bad Request");
			$msg = 'Bad Request';
		case 404:
			header("HTTP/1.1 404 Not Found");
			$msg = '404 not found';
		case 401:
			header('HTTP/1.1 401 Unauthorized');
			$msg = 'Unauthorized';
		case 500:
			header('HTTP/1.1 500 Internal Server Error');
		case 411:
			header("HTTP/1.1 411 Length Required");
		case 415:
			header("HTTP/1.1 415 Unsupported Media Type");
		}

		if (defined('DEBUG')) {
			header("Content-Type: text/plain; charset=utf-8");
			print "DASe Error Report\n\n";
			print "================================\n";
			print "[http_error_code] => $code\n";
			print $this->request;
			print "================================\n";
		} else {
			//todo: pretty error message for production
			header("Content-Type: text/plain; charset=utf-8");
			print "DASe Error Report\n\n";
			print "[http_error_code] => $code\n";
		}
		exit;
	}

	function __destruct() 
	{
		Dase_Log::debug('finished request '.Dase_Timer::getElapsed());
	}
}

