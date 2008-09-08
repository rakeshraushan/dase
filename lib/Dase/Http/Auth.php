<?php

class Dase_Http_Auth
{
	/** an entity, such as tag or collection will have ONE password
	 * for a given eid.  This simply "authenticates" the user.
	 * authorization happens after the eid is verified.  After that,
	 * authorization level will be determined based on other criteria
	 */
	public static function getEid()
	{
		if (isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW'])) {
			$eid = $_SERVER['PHP_AUTH_USER'];
			$passwords[] = substr(md5(Dase_Config::get('token').$eid.'httpbasic'),0,12);

			//for service users:
			$service_users = Dase_Config::get('serviceuser');
			if (isset($service_users[$eid])) {
				$passwords[] = md5(Dase_Config::get('service_token').$eid);
			}

			//lets me use the superuser passwd for http work
			$su = Dase_Config::get('superuser');
			if (isset($su[$eid])) {
				$passwords[] = $su[$eid];
			}

			if (in_array($_SERVER['PHP_AUTH_PW'],$passwords)) {
				Dase_Log::debug('accepted user '.$eid.' using password '.$_SERVER['PHP_AUTH_PW']);
				return $eid;
			} else {
				Dase_Log::debug('rejected user '.$eid.' using password '.$_SERVER['PHP_AUTH_PW']);
			}
		}
		header('WWW-Authenticate: Basic realm="DASe"');
		header('HTTP/1.1 401 Unauthorized');
		echo "sorry, authorized users only";
		exit;
	}
}

