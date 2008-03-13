<?php

class EiduserModuleHandler {

	public static function login() {
		if (!extension_loaded("eid")) {
			dl("eid.so");
		}
		if (!extension_loaded("eid")) {
			echo "The eid extension is not loaded into this PHP!<p>\n";
			print_r (get_loaded_extensions());
			exit;
		}
		if (!function_exists("eid_decode")) {
			echo "The eid_decode function is not available in this eid extension!<p>\n";
			print_r (get_extension_funcs ("eid"));
			exit;
		}

		$ut_user = eid_decode(); 

		if (isset($ut_user->status) && EID_ERR_OK != $ut_user->status) {
			unset($ut_user);
		}
		if ($ut_user == NULL) {
			$url = APP_ROOT . '/modules/eidauth/login';
			header ("Set-Cookie: DOC=$url; path=/; domain=.utexas.edu;");
			header ("Location: https://utdirect.utexas.edu");
			echo "Serious EID decode error - is there an FC cookie?";
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

			$db = Dase_DB::get();
			$sql = "
				SELECT * FROM dase_user 
				WHERE lower(eid) = ?
				";	
			$sth = $db->prepare($sql);
			$sth->execute(array(strtolower($ut_user->eid)));
			$row = $sth->fetch();
			if ($row) {
				$db_user = new Dase_DBO_DaseUser($row);
			} else {
				$db_user = new Dase_DBO_DaseUser();
				$db_user->name = $ut_user->name; 
				$db_user->eid = $ut_user->eid; 
				$db_user->insert();
			}
			Dase_Cookie::set($db_user->eid);
			Dase::redirect("login/$db_user->eid");
		}
	}

	public static function logoff() {
		setcookie('DOC','',time()-86400,'/','.utexas.edu');
		setcookie('FC','',time()-86400,'/','.utexas.edu');
		setcookie('SC','',time()-86400,'/','.utexas.edu');
		setcookie('TF','',time()-86400,'/','.utexas.edu');
		Dase::redirect('/');
	}
}
