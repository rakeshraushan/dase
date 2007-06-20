<?php

class Dase_Eid_Plugin extends Dase_Plugin
{
	public function load() {
	}

	public function beforeDisplay() {
#		Dase_Template::instance()->assign('msg','Dase_Eid_Plugin loaded!');
	}

	public function beforeLoginForm() {
#Dase::reload('/plugins/Dase/Eid/target.php');
		header("Location:http://dase.lib.utexas.edu/plugins/Dase/Eid/target.php");
	}

	public function beforeLoginFormOld() {
		$username = trim($_SERVER['HTTP_X_EID']);
		if ($username) {
			$user = Dase_User::check_credentials($username,'pass');
			if ($user) {
				$cookie = new Dase_AuthCookie($user->id);
				$cookie->set();
				Dase::reload();
			} else {
				//simply add the user
				$person_array =	Dase_Eid_Ldap::lookup($username,'uid');
				$new_user = new Dase_DB_DaseUser;
				if (isset($person_array[$username]['name']) && isset($person_array[$username]['eid'])) {
					die('DASE@lib is currently under development.  Check back soon!');
					$new_user->name = $person_array[$username]['name'];
					$new_user->eid = $person_array[$username]['eid'];
					$new_user->insert();
				} else {
					$new_user->name = "$username (temporary login)";
					$new_user->eid = $username;
					$new_user->insert();
				}
				$user = Dase_User::check_credentials($username,'pass');
				if ($user) {
					$cookie = new Dase_AuthCookie($user->id);
					$cookie->set();
					Dase::reload();
				} else {
					Dase::reload('error','there was an authentication problem');
				}
			}
		}
	}

	public function moduleFilter() {
		$params = func_get_args();
		if (isset($params[0])) {
			$string = $params[0];
			if ('eid' == $string) {
				$string = 'eid_';
			}
			return $string;
		}
	}

	public function beforeLogoff() {
		setcookie('DOC','',time()-86400,'/','.utexas.edu');
		setcookie('FC','',time()-86400,'/','.utexas.edu');
		setcookie('SC','',time()-86400,'/','.utexas.edu');
		setcookie('TF','',time()-86400,'/','.utexas.edu');
	}
}

