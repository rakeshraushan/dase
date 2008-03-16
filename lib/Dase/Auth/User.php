<?php

class Dase_Auth_User
{
	public function authorize($collection_ascii_id,$eid)
	{
		$user = new Dase_User();
		if ($collection_ascii_id) {
			if ($user->checkAuth($collection_ascii_id,'read')) {
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

