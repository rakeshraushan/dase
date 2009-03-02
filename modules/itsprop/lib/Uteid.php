<?php
class Uteid 
{
	public static function login($db,$r)
	{
		if (!extension_loaded("eid")) {
			dl("eid.so");
		}
		if (!extension_loaded("eid")) {
			echo "The eid extension is not loaded into this PHP!<p>\n";
			print_r (get_loaded_extensions());
			exit;
		}
		if (!function_exists("eid_decode"))
		{
			echo "The eid_decode function is not available in this eid extension!<p>\n";
			print_r (get_extension_funcs ("eid"));
			exit;
		}

		$ut_user = eid_decode(); 

		if (isset($ut_user->status) && EID_ERR_OK != $ut_user->status) {
			unset($ut_user);
		}
		if ($ut_user == NULL) {
			$url = $r->app_root.'/modules/'.$r->module.'/login';
			header ("Set-Cookie: DOC=$url; path=/; domain=.utexas.edu;");
			header ("Location: https://utdirect.utexas.edu");
			echo "user is not logged in";
			exit;
		}
		if ($ut_user) {
			switch ($ut_user->status) {
			case EID_ERR_OK:
				//echo "EID decode ok<br>\n";
				break;
			case EID_ERR_INVALID:
				echo "Invalid EID encoding";
				exit;
			case EID_ERR_BADARG:
				echo "Internal error in EID decoding";
				exit;
			case EID_ERR_BADSIG:
				echo "Invalid EID signature";
				exit;
			}
			$db_user = $r->retrieve('user');
			if (!$db_user->retrieveByEid($ut_user->eid)) {
				$db_user->eid = strtolower($ut_user->eid); 
				$db_user->name = $ut_user->name; 
				$db_user->insert();
			}
			$r->setCookie('eid',$db_user->eid);
			return $db_user;
		}
	}

	/**
	 * this method will be called
	 * w/ an http delete to '/login' *or* '/login/{eid}'
	 *
	 */
	public static function logout($r)
	{
		setcookie('DOC','',time()-86400,'/','.utexas.edu');
		setcookie('FC','',time()-86400,'/','.utexas.edu');
		setcookie('SC','',time()-86400,'/','.utexas.edu');
		setcookie('TF','',time()-86400,'/','.utexas.edu');
	}

}
