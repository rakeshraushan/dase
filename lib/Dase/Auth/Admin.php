<?php

class Dase_Auth_Admin
{
	public function authorize($collection_ascii_id,$eid)
	{
		//the checkAuth methods use the collection manager
		//table to look-up privilege level
		$user = new Dase_User;
		Dase_Registry::set('user',$user->db_user);
		if ($user->eid == $eid && $user->checkAuth($collection_ascii_id,'admin')) {
			return true;
		}
		return false;
	}
}

