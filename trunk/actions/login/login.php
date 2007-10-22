<?php

// method: POST

//add a hook here to redirect to alternative login?

$username = Dase::filterPost('username');
$password = Dase::filterPost('password');
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
// if login fails:
Dase::reload('/login',$msg);
