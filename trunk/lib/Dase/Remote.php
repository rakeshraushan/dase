<?php

class Dase_Remote 
{
	protected $ctx;
	protected $url;

	public function __construct($url,$user='',$pass='',$method='GET') {
		$this->url = trim($url,"/");
		$header = '';
		if ($user && $pass) {
			$auth = base64_encode("$user:$pass");
			$header = "Authorization: Basic $auth";
		} 
		$opts = array(
			'http' => array (
				'method'=>$method,
				'header'=>$header
			));
		$this->ctx = stream_context_create($opts);
	}

	public function get() {
		return file_get_contents($this->url,false,$this->ctx);
	}

}
