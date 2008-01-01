<?php

//xhr has already gotten the user logged out
//this is the completion of the logoff link click

if (Dase::getConf('login_module')) {
	$module = Dase::getConf('login_module');
	Dase::reload("modules/$module/logoff");
} else {
	Dase::reload();
	exit;
}
