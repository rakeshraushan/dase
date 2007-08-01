<?php

Dase_Plugins::act('dase','before_logoff');
$user = Dase::getUser();
$cookie = new Dase_AuthCookie($user->id);
$cookie->logout();
header("Location:http://www.lib.utexas.edu");
exit;
