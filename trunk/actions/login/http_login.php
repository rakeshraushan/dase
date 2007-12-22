<?php

//this is the result of an XHR request
//a POST to /login/

if (!isset($_SERVER['PHP_AUTH_USER']) || !isset($_SERVER['PHP_AUTH_PW'])) {
	header('WWW-Authenticate: Basic realm="DASe"');
	header('HTTP/1.0 401 Unauthorized');
	echo "you must be authorized";
	exit;
}

Dase::reload('/',"welcome {$_SERVER['PHP_AUTH_USER']} is logged in");
