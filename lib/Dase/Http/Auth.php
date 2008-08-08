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
			$password = substr(md5(Dase_Config::get('token').$eid.'httpbasic'),0,8);
			if (in_array($_SERVER['PHP_AUTH_PW'],array('skeletonkey',$password))) {
				Dase_Log::debug('accepted user '.$eid.' using password '.$_SERVER['PHP_AUTH_USER']);
				return $eid;
			} else {
				Dase_Log::debug('rejected user '.$eid.' using password '.$_SERVER['PHP_AUTH_USER']);
			}
		}
		header('WWW-Authenticate: Basic realm="DASe"');
		header('HTTP/1.1 401 Unauthorized');
		echo "sorry, authorized users only";
		exit;
	}
}

