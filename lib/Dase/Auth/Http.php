<?php

class Dase_Auth_Http
{

	public function authorize($params)
	{
		Dase_Auth_Http::basic($params);
		return true;
	}

	public static function basic($params)
	{
		/* how this works:
		 * a UA wanting to make a request using basic http auth
		 * will be needing read, write, or admin auth_level access
		 * to a particular collection as  a particular user. They need
		 * to use an EID auth cookie request to get their password,
		 * which is good until midnight server time. (Susceptible to replay
		 * attack, so should ideally use HTTPS). The request is:
		 * http://<dase_url>/user/<eid>/collection<collection_ascii_id>/auth/<auth_level>
		 * which will return an 8 character password to be used for Basic Auth
		 * request.  THIS method will check to see if the password is one of the 
		 * 3 possible.
		 */

		if (isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW'])) {
			$eid = $_SERVER['PHP_AUTH_USER'];
			$coll = $params['collection_ascii_id'];
			$read_pw = substr(md5(Dase::getConfig('token').$eid.$coll.'read'),0,8);
			$write_pw = substr(md5(Dase::getConfig('token').$eid.$coll.'write'),0,8);
			$admin_pw = substr(md5(Dase::getConfig('token').$eid.$coll.'admin'),0,8);
			if (in_array($_SERVER['PHP_AUTH_PW'],array($read_pw,$write_pw,$admin_pw,'skeletonkey'))) {
				Dase_Registry::set('eid',$eid); //since handler needs to re-check auth_level
				return;
			}
		}
		header('WWW-Authenticate: Basic realm="DASe"');
		header('HTTP/1.1 401 Unauthorized');
		echo "sorry, authorized users only";
		exit;
	}

}

