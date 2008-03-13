<?php

class Dase_Auth_Superuser
{
	public function authorize($collection_ascii_id,$eid) {
		//the checkAuth methods use the collection manager
		//table to look-up privilege level
		$user = new Dase_User();
		if (in_array($user->eid,Dase::getConf('superuser'))) {
			return true;
		}
		return false;
	}

}

