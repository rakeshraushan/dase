<?php

class Dase_Auth_Write
{
	public function authorize($params)
	{
		if (isset($params['eid']) && 
			isset($params['collection_ascii_id'])) {
				$user = new Dase_User;
				if ($user->eid == $params['eid'] && 
					$user->checkAuth($params['collection_ascii_id'],'write')) {
						return true;
					}
			}
		return false;
	}
}

