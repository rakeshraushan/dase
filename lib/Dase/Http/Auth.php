<?php

class Dase_Http_Auth
{
	/** an entity, such as tag or collection will have ONE password
	 * for a given eid.  This simply "authenticates" the user.
	 * authorization happens after the eid is verified.  After that,
	 * authorization level will be determined based on other criteria
	 */
	public static function getEid($check_db = false)
	{
		$request_headers = apache_request_headers();
		if (isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW'])) {
			$eid = $_SERVER['PHP_AUTH_USER'];
			$passwords[] = substr(md5(Dase_Config::get('token').$eid.'httpbasic'),0,12);

			//xmlhttp requests are still fresh for one day after pwd changes
			//Dase_Log::debug(print_r($request_headers,true));
			if (isset($request_headers['X-Requested-With']) &&
				"XMLHttpRequest" == $request_headers['X-Requested-With']) 
			{
				$passwords[] = substr(md5(Dase_Config::get('old_token').$eid.'httpbasic'),0,12);
			}


			//for service users:
			$service_users = Dase_Config::get('serviceuser');
			//if eid is among service users, get password w/ service_token as salt
			if (isset($service_users[$eid])) {
				$passwords[] = md5(Dase_Config::get('service_token').$eid);
			}

			//lets me use the superuser passwd for http work
			$su = Dase_Config::get('superuser');
			if (isset($su[$eid])) {
				$passwords[] = $su[$eid];
			}

			if ($check_db) {
				$u = Dase_DBO_DaseUser::get($eid);
				$pass_md5 = md5($_SERVER['PHP_AUTH_PW']);
				if ($pass_md5 == $u->service_key_md5) {
					Dase_Log::debug('accepted user '.$eid.' using password '.$_SERVER['PHP_AUTH_PW']);
					return $eid;
				}
			}

			if (in_array($_SERVER['PHP_AUTH_PW'],$passwords)) {
				Dase_Log::debug('accepted user '.$eid.' using password '.$_SERVER['PHP_AUTH_PW']);
				return $eid;
			} else {
				Dase_Log::debug('rejected user '.$eid.' using password '.$_SERVER['PHP_AUTH_PW']);
			}
		} else {
			Dase_Log::debug(print_r($request_headers,true));
			Dase_Log::debug('PHP_AUTH_USER and/or PHP_AUTH_PW not set');
		}
		header('WWW-Authenticate: Basic realm="DASe"');
		header('HTTP/1.1 401 Unauthorized');
		echo "sorry, authorized users only";
		exit;
	}
}

