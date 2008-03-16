<?php

class Dase_Auth_Read
{
	public function authorize($collection_ascii_id,$eid)
	{
		//the checkAuth methods use the collection manager
		//table to look-up privilege level
		$user = new Dase_User();
		if ($user->eid == $eid &&
			$user->checkAuth($collection_ascii_id,'read')) {
				return true;
			}
		return false;
	}
}

