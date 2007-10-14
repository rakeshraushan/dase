<?php

//add a hook here?

$user = Dase::getUser();
$cookie = new Dase_AuthCookie($user->id);
$cookie->logout();
$user_cookie = new Dase_UserCookie;
$user_cookie->delete();
Dase::reload();
//header("Location:http://www.lib.utexas.edu");
exit;
