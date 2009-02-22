<?php
Class Dase_Http 
{
	function __construct() {}

	public static function put($url,$body,$user,$pass,$mime_type='')
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
		curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
		curl_setopt($ch, CURLOPT_USERPWD,$user.':'.$pass);
		if ($mime_type) {
			$headers  = array(
				"Content-Type: $mime_type"
			);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		}
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$result = curl_exec($ch);
		$info = curl_getinfo($ch);
		curl_close($ch);  
		if ('200' == $info['http_code']) {
			return 'ok';
		} else {
			return $result;
		}
	}
}

