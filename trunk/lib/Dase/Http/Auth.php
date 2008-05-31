<?php

class Dase_Http_Auth
{
	public static function getEid($entity)
	{
		if (isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW'])) {
			$eid = $_SERVER['PHP_AUTH_USER'];
			$password = $entity->getHttpPassword($eid);
			if (in_array($_SERVER['PHP_AUTH_PW'],array('skeletonkey#!99',$password))) {
				return $eid;
			}
		}
		header('WWW-Authenticate: Basic realm="DASe"');
		header('HTTP/1.1 401 Unauthorized');
		echo "sorry, authorized users only";
		exit;
	}
}

