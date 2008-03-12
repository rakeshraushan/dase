<?php

class Dase_Auth_Eid
{
	public function authorize($dase,$collection_ascii_id='',$eid) {
		$dase->user = new Dase_User();
		if ($dase->user->eid == $eid) {
			return true;
		}
		return false;
	}

}

