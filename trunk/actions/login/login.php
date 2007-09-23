<?php

//add a hook here to redirect to alternative login?

$username = Dase::filterPost('username');
$password = Dase::filterPost('password');
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
$tpl->assign('content','login_form'); //set content template
$tpl->display();
