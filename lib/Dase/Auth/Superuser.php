<?php

class Dase_Auth_Superuser
{
	public function authorize($dase,$collection_ascii_id,$eid) {
		//the checkAuth methods use the collection manager
		//table to look-up privilege level
		$dase->user = new Dase_User();
		if (in_array($dase->user->eid,Dase::getConf('superuser'))) {
			return true;
		}
		return false;
	}

}

