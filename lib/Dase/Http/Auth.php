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

	public function getEid($log,$check_db = false)
	{
		$request_headers = apache_request_headers();
		$passwords = array();
		$log->debug(print_r($request_headers,true));

		if ($this->htuser && $this->htpass) {
			$eid = $this->htuser;
			$passwords[] = substr(md5($this->token.$eid.'httpbasic'),0,12);

			//for service users:
			$service_users = $auth['serviceuser'];
			//if eid is among service users, get password w/ service_token as salt
			if (isset($service_users[$eid])) {
				$passwords[] = md5($this->service_token.$eid);
			}

			//lets me use the superuser passwd for http work
			if (isset($this->superuser[$eid])) {
				$passwords[] = $this->superuser[$eid];
			}

			//this is used for folks needing a quick service pwd to do uploads
			if ($check_db) {
				$u = Dase_DBO_DaseUser::get($eid);
				$pass_md5 = md5($this->htpass);
				if ($pass_md5 == $u->service_key_md5) {
					$log->debug('accepted user '.$eid.' using password '.$this->htpass);
					return $eid;
				}
			}

			if (in_array($this->htpass,$passwords)) {
				$log->debug('accepted user '.$eid.' using password '.$this->htpass);
				return $eid;
			} else {
				$log->debug('rejected user '.$eid.' using password '.$this->htpass);
			}
		} else {
			$log->debug('PHP_AUTH_USER and/or PHP_AUTH_PW not set');
		}
		header('WWW-Authenticate: Basic realm="DASe"');
		header('HTTP/1.1 401 Unauthorized');
		echo "sorry, authorized users only";
		exit;
	}
}

