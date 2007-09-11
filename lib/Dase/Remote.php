<?php

class Dase_Remote 
{
	private $ctx;
	private $url;

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

	public function getCollectionInfo($ascii_id) {
		$url = $this->url . '/collection/' . $ascii_id;
		return file_get_contents($url,false,$this->ctx);
	}

	public function getAll() {
		$url = $this->url . '/collections';
		return file_get_contents($url,false,$this->ctx);
	}

	public function getAdminAttributes() {
		$url = $this->url . '/admin_attributes';
		return file_get_contents($url,false,$this->ctx);
	}

	public function getAttributes($ascii_id) {
		$url = $this->url . "/collection/$ascii_id/attributes";
		return file_get_contents($url,false,$this->ctx);
	}

	public function getItem($ser_num,$ascii_id) {
		$url = $this->url . "/collection/$ascii_id/item/$ser_num";
		return file_get_contents($url,false,$this->ctx);
	}

	public function getItemSerNums($ascii_id) {
		$url = $this->url . "/collection/$ascii_id/items?ser_nums=1";
		return file_get_contents($url,false,$this->ctx);
	}
}
