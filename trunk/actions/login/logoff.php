<?php

//add a hook here?

$user = Dase::getUser();
$cookie = new Dase_AuthCookie($user->id);
$cookie->logout();
header("Location:http://www.lib.utexas.edu");
exit;
