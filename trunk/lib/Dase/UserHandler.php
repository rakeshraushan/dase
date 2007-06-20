<?php

class Dase_UserHandler 
{
	public static function index() {
		$coll = new Dase_DB_Collection;
		$tpl = Dase_Template::instance();
		$tpl->assign('collections',$coll->getAll());
		$tpl->assign('content','collections');
		$tpl->display();
	}

	public static function loginForm() {
		Dase_Plugins::act('dase','before_login_form');
		$tpl = Dase_Template::instance();
		$tpl->assign('content','login');
		$tpl->display('page.tpl');
	}

	public static function login() {
		Dase_Plugins::act('dase','before_login');
		$username = Dase_Utils::filterPost('username');
		$password = Dase_Utils::filterPost('password');
		$tpl = Dase_Template::instance();
		$msg = '';
		//largely from Advanced PHP Programming p. 338
		if ($username && $password) {
			try {
				$user = Dase_User::check_credentials($username,$password);
				if ($user) {
					$cookie = new Dase_AuthCookie($user->id);
					$cookie->set();
					Dase::reload('/',"Hello $user->name");
				} else {
					$msg = 'incorrect username/password combination';
				}
			} catch (AuthException $e) {
				$msg = 'invalid login';
			}
		}
		$tpl->assign('msg',$msg); 
		$tpl->assign('content','login'); //set content template
		$tpl->display();
	}

	public static function logoff() {
		Dase_Plugins::act('dase','before_logoff');
		$user = Dase::getUser();
		$cookie = new Dase_AuthCookie($user->id);
		$cookie->logout();
		header("Location:http://www.lib.utexas.edu");
		exit;
	}
}
