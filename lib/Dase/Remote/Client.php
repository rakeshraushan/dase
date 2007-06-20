<?php

require_once 'Dase/Log.php';
require_once 'Zend/Http/Client.php';

class Dase_Remote_Client extends Zend_Http_Client
{
	public function __construct($site,$user='',$pass='') {
		parent::__construct();
		$this->setUri($site);
		if ($user && $pass) {
			$this->setAuth($user,$pass);
		}
	}

	public function setPath($path) {
		$uri = $this->getUri(true);
		$this->setUri($uri . '/' . $path);
	}

	public function getXml() {
		Dase_Log::logRemoteCall($this->getUri());
		$resp = $this->request();
		return $resp->getBody();
	}
}
