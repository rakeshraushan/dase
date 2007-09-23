<?php
class Dase_Remote_Collection extends Dase_Remote
{
	public $ascii_id;

	public function __construct($url,$ascii_id,$user='',$pass='',$method='GET') {
		$this->ascii_id = $ascii_id;
		parent::__construct($url,$user,$pass,$method);
	}

	public static function listAll($url,$user,$pass) {
		$url = $url . '/collections';
		$rc = new Dase_Remote($url,$user,$pass);
		return $rc->get();
	}

	public function getCollectionInfo() {
		$url = $this->url . '/collection/' . $this->ascii_id;
		return file_get_contents($url,false,$this->ctx);
	}

	public function getAdminAttributes() {
		$url = $this->url . '/admin_attributes';
		return file_get_contents($url,false,$this->ctx);
	}

	public function getAttributes() {
		$url = $this->url . "/collection/$this->ascii_id/attributes";
		return file_get_contents($url,false,$this->ctx);
	}

	public function getItem($ser_num) {
		$url = $this->url . "/collection/$this->ascii_id/item/$ser_num";
		return file_get_contents($url,false,$this->ctx);
	}

	public function getItemSerNums() {
		$url = $this->url . "/collection/$this->ascii_id/items?ser_nums=1";
		return file_get_contents($url,false,$this->ctx);
	}
}

