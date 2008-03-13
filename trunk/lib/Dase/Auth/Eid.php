<?php

class Dase_Auth_Eid
{
	public function authorize($collection_ascii_id='',$eid) {
		$user = new Dase_User();
		if ($user->eid == $eid) {
			return true;
		}
		return false;
	}

}

