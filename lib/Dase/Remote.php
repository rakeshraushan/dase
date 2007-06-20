<?php

require_once 'Dase/Remote/Client.php';

class Dase_Remote 
{
	private $url;
	private $user;
	private $pass;

	public function __construct($url,$user,$pass) {
		$this->url = $url;
		$this->user = $user;
		$this->pass = $pass;
	}	

	public function getCollectionInfo($ascii_id) {
		$remote = new Dase_Remote_Client($this->url,$this->user,$this->pass);
		$remote->setPath("collection/$ascii_id");
		return 	$remote->getXml();
	}

	public function getAll() {
		$remote = new Dase_Remote_Client($this->url,$this->user,$this->pass);
		$remote->setPath("collections");
		return $remote->getXml();
	}

	public function getAdminAttributes() {
		$remote = new Dase_Remote_Client($this->url,$this->user,$this->pass);
		$remote->setPath("admin_attributes");
		return $remote->getXml();
	}

	public function getAttributes($ascii_id) {
		$remote = new Dase_Remote_Client($this->url,$this->user,$this->pass);
		$remote->setPath("collection/$ascii_id/attributes");
		return $remote->getXml();
	}

	public function getItem($ser_num,$ascii_id) {
		$remote = new Dase_Remote_Client($this->url,$this->user,$this->pass);
		$remote->setPath("collection/$ascii_id/item/$ser_num");
		return $remote->getXml();
	}
	public function getItemSerNums($ascii_id) {
		$remote = new Dase_Remote_Client($this->url,$this->user,$this->pass);
		$remote->setPath("collection/$ascii_id/items?ser_nums=1");
		return $remote->getXml();
	}
}
