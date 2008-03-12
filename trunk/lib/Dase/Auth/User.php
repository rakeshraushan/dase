<?php

class Dase_Auth_User
{
	public function authorize($dase,$collection_ascii_id,$eid) {
		$dase->user = new Dase_User();
		if ($collection_ascii_id) {
			if ($dase->user->checkAuth($collection_ascii_id,'read')) {
				return true;
			}
		} else {
			if ($dase->user->eid) {
				return true;
			}
		}
		return false;
	}
}

