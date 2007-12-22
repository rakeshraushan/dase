<?php
///the idea is that you can initiate the login process simply by sending a "GET"
//to the '/login/' resource, which will bring you here

if (Dase::getConf('login_module')) {
	$module = Dase::getConf('login_module');
	Dase::reload("modules/$module");
} else {
	Dase::error('no authentication mechanism configured');
	exit;
}
