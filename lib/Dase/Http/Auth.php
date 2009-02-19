<?php

class Dase_Http_Auth
{
	public function __construct($auth,$htuser='',$htpass='')
	{
		$this->token = $auth['token'];
		$this->serviceuser = $auth['serviceuser'];
		$this->service_token = $auth['service_token'];
		$this->superuser = $auth['superuser'];
		$this->htuser = $htuser;
		$this->htpass = $htpass;
	}

	public function setUser($htuser,$htpass)
	{
		if (!$this->htuser) {
			$this->htuser = $htuser;
		}
		if (!$this->htpass) {
			$this->htpass = $htpass;
		}
	}

	public function getSuperusers()
	{
		return $this->superuser;
	}

}

