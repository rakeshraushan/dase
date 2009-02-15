<?php

class Dase_Http_Auth
{
	public function __construct($auth,$server)
	{
		$this->token = $auth['token'];
		$this->serviceuser = $auth['serviceuser'];
		$this->service_token = $auth['service_token'];
		$this->superuser = $auth['superuser'];
		if (isset($server['PHP_AUTH_USER'])) {
			$this->htuser = $server['PHP_AUTH_USER'];
		} else {
			$this->htuser = '';
		}
		if (isset($server['PHP_AUTH_PW'])) {
			$this->htpass = $server['PHP_AUTH_PW'];
		} else {
			$this->htpass = '';
		}
	}

	public function getEid($check_db = false)
	{
		$request_headers = apache_request_headers();
		$passwords = array();
		Dase_Log::get()->debug(print_r($request_headers,true));

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
					Dase_Log::get()->debug('accepted user '.$eid.' using password '.$this->htpass);
					return $eid;
				}
			}

			if (in_array($this->htpass,$passwords)) {
				//Dase_Log::get()->debug('accepted user '.$eid.' using password '.$this->htpass);
				return $eid;
			} else {
				//Dase_Log::get()->debug('rejected user '.$eid.' using password '.$this->htpass);
			}
		} else {
			//Dase_Log::get()->debug('PHP_AUTH_USER and/or PHP_AUTH_PW not set');
		}
		header('WWW-Authenticate: Basic realm="DASe"');
		header('HTTP/1.1 401 Unauthorized');
		echo "sorry, authorized users only";
		exit;
	}
}

