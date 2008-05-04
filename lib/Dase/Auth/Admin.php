<?php

class Dase_Auth_Admin
{
	public function authorize($params,$type)
	{
		if ('collection' == $type && isset($params['eid'])) {
			$user = new Dase_User;
			if ($user->eid == $params['eid'] && 
				$user->checkCollectionAuth($params['collection_ascii_id'],'admin')) {
					return true;
				}
		}
		if ('tag' == $type && isset($params['eid'])) {
			$user = new Dase_User;
			if ($user->eid == $params['eid'] && 
				$user->checkTagAuth($params['tag_ascii_id'],'admin')) {
					return true;
				}
		}
		return false;
	}
}

