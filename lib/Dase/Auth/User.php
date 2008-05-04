<?php

class Dase_Auth_User
{
	public function authorize($params,$type)
	{
		$user = new Dase_User();
		if ('collection' == $type) {
			if ($user->checkCollectionAuth($params['collection_ascii_id'],'read')) {
				return true;
			}
		} elseif ('tag' == $type) {
			if ($user->checkTagAuth($params['tag_ascii_id'],'read')) {
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

