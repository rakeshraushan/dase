<?php

class Dase_Http_Response
{
	private static $codes = array(
		"100" => "Continue ",
		"101" => "Switching Protocols ",
		"200" => "OK ",
		"201" => "Created ",
		"202" => "Accepted ",
		"203" => "Non-Authoritative Information ",
		"204" => "No Content ",
		"205" => "Reset Content ",
		"206" => "Partial Content ",
		"300" => "Multiple Choices ",
		"301" => "Moved Permanently ",
		"302" => "Found ",
		"303" => "See Other ",
		"304" => "Not Modified ",
		"305" => "Use Proxy ",
		"306" => "(Unused) ",
		"307" => "Temporary Redirect ",
		"400" => "Bad Request ",
		"401" => "Unauthorized ",
		"402" => "Payment Required ",
		"403" => "Forbidden ",
		"404" => "Not Found ",
		"405" => "Method Not Allowed ",
		"406" => "Not Acceptable ",
		"407" => "Proxy Authentication Required ",
		"408" => "Request Timeout ",
		"409" => "Conflict ",
		"410" => "Gone ",
		"411" => "Length Required ",
		"412" => "Precondition Failed ",
		"413" => "Request Entity Too Large ",
		"414" => "Request-URI Too Long ",
		"415" => "Unsupported Media Type ",
		"416" => "Requested Range Not Satisfiable ",
		"417" => "Expectation Failed ",
		"500" => "Internal Server Error ",
		"501" => "Not Implemented ",
		"502" => "Bad Gateway ",
		"503" => "Service Unavailable ",
		"504" => "Gateway Timeout ",
		"505" => "HTTP Version Not Supported ",
	);

	private $request;

	public function __construct($request)
	{
		$this->request =  $request;
		$this->mime_type = $request->response_mime_type;
	}

	public function render($content,$set_cache=true,$status_code=null)
	{
		if ($set_cache) {
			$cache_id = $this->request->getCacheId();
			$this->request->retrieve('cache')->setData($cache_id,$content);
		}
		if ($status_code) {
			$message = $status_code.' '.self::$codes[$status_code]; 
			header("HTTP/1.1 $message");
		}
		header("Content-Type: ".$this->mime_type."; charset=utf-8");
		echo $content;
		exit;
	}

	public function serveFile($path,$mime_type,$download=false,$base_path)
	{
		if (!file_exists($path)) {
			header('Content-Type: image/jpeg');
			readfile($base_path.'/www/images/unavail.jpg');
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

	public function redirect($path='',$params=null,$code=303)
	{
		//SHOULD use 303 (redirect after put,post,delete)
		//OR 307 -- no go -- look here
		//NOTE that this redirect may be innapropriate when
		//client expect something OTHER than html (e.g., json,text,xml)
		//format should be passed in params
		$query_array = array();
		if (isset($params) && is_array($params)) {
			foreach ($params as $key => $val) {
				$query_array[] = urlencode($key).'='.urlencode($val);
			}
		}
		$app_root = $this->request->app_root;
		if ('http' != substr($path,0,4)) {
			$redirect_path = trim($app_root,'/') . "/" . trim($path,'/');
		} else {
			$redirect_path = $path;
		}
		if (count($query_array)) {
			//since path is allowed to have some query params already
			if (false !== strpos($path,'?')) {
				$redirect_path .= '&'.join("&",$query_array);
			} else {
				$redirect_path .= '?'.join("&",$query_array);
			}
		}
		$this->request->logger()->info('redirecting to '.$redirect_path);
		header("Location:". $redirect_path,TRUE,$code);
		exit;
	}

	public function error($code,$msg='',$log_error = true)
	{
		if (isset(self::$codes[$code])) {
			$message = $code.' '.self::$codes[$code]; 
			header("HTTP/1.1 $message");
		} else {
			header("HTTP/1.1 500 Internal Server Error");
		}
		if ($msg) {
			$this->request->error_message = $msg;
		}
		$error_text = '';
		if (ini_get('display_errors')) {
			header("Content-Type: text/plain; charset=utf-8");
			$error_text .= "DASe Error Report\n\n";
			$error_text .= "================================\n";
			$error_text .= "[http_error_code] => $code\n";
			$error_text .= $this->request->getLogData();
			$error_text .= "================================\n";
		} else {
			//todo: pretty error message for production
			header("Content-Type: text/plain; charset=utf-8");
			$error_text .= "DASe Error Report\n\n";
			$error_text .= "[http_error_code] => $code\n";
		}
		if ($log_error) {
			$this->request->logger()->debug($error_text);
		}
		if ($msg) {
			print $msg;
		} else {
			print $error_text;
		}
		exit;
	}

	public function atomError($code,$msg='')
	{
		$feed = new Dase_Atom_Feed;
		if (isset(self::$codes[$code])) {
			$message = $code.' '.self::$codes[$code]; 
		}
		$feed->setTitle($message . ' '.$msg);
		$feed->addAuthor();
		$feed->addCategory('error','http://daseproject.org/category/error');
		echo $feed->asXml();
		exit;
	}

	public function ok($msg = '')
	{
		header("HTTP/1.1 200 Ok");
		print $msg;
		exit;
	}

	function __destruct() 
	{
		//see http://bugs.php.net/bug.php?id=34206
		// if strange 'failed to open stream' messages appear
		$this->request->logger()->debug('finished request '.$this->request->getElapsed());
	}
}

