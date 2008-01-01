<?php

//requests coming here w/ user set as 'logoff' will get through
//and consequently they'll need to re-login to go anywhere else
//note that this page is meant to be requested by an XHR

if (
	!isset($_SERVER['PHP_AUTH_USER']) || 
	'logoff' != $_SERVER['PHP_AUTH_USER']
) {
	header('WWW-Authenticate: Basic realm="DASe"');
	header('HTTP/1.0 401 Unauthorized');
	exit;
} else {
	header('HTTP/1.0 403 Bad Request'); 
	echo "Unauthorized";
	exit;
}
