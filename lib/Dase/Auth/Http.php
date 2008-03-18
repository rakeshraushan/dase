<?php

class Dase_Auth_Http
{

	public function authorize($params)
	{
		Dase_Auth_Http::basic();
		return true;
	}

	public static function basic()
	{
		//from php cookbook 2nd ed. p 240
		if (isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW'])) {
			//HARDCODED (change that!)
			if (('dase2' == $_SERVER['PHP_AUTH_USER']) && ('api' == $_SERVER['PHP_AUTH_PW'])) {
				return;
			}
		}
		header('WWW-Authenticate: Basic realm="DASe"');
		header('HTTP/1.1 401 Unauthorized');
		echo "sorry, authorized users only";
		exit;
	}

}

