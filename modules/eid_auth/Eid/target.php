<?php
define('APP_ROOT','https://dase.lib.utexas.edu');
//ini_set(display_arrors,1);
//error_reporting(E_ALL);
if (isset($_SERVER['HTTP_X_EID'])) {
	$eid = $_SERVER['HTTP_X_EID'];
	include '../../../lib/Dase/AuthCookie.php';
	include '../../../lib/Dase/Encryption.php';
	include '../../../lib/Dase/Session.php';
	$cookie = new Dase_AuthCookie($eid);
	$cookie->set();
	include '../../../lib/Dase.php';
	Dase::redirect();
}

