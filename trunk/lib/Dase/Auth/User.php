<?php

class Dase_Auth_User
{
	public function authorize($params)
	{
		$user = new Dase_User();
		if (isset($params['collection_ascii_id']) && $params['collection_ascii_id']) {
			if ($user->checkAuth($params['collection_ascii_id'],'read')) {
				return true;
			}
		} else {
			if ($user->eid) {
				return true;
			}
		}
		return false;
	}
}

