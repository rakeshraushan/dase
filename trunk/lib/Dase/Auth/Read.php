<?php

class Dase_Auth_Read
{
	public function authorize($params)
	{
		if (!isset($params['eid'])) {
			//in some cases (basic http auth), eid will not be in url
			$params['eid'] = $_SERVER['PHP_AUTH_USER'];
		}
		if ($params['eid'] && isset($params['collection_ascii_id'])) {
			$user = new Dase_User;
			if ($user->eid == $params['eid'] && 
				$user->checkAuth($params['collection_ascii_id'],'read')) {
					return true;
				}
		}
		return false;
	}
}

