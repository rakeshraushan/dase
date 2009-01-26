<?php
Class Dase_Auth 
{
	function __construct() {}

	public static function getSecret($key)
	{
		return md5(Dase_Config::get('token').$key);
	}

	public static function getServicePassword($serviceuser)
	{
		return md5(Dase_Config::get('service_token').$serviceuser);
	}

}

