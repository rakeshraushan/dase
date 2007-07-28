<?php

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
			$cookie = new Dase_AuthCookie($user->eid);
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
