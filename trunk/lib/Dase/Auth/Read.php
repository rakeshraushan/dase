<?php

class Dase_Auth_Read
{
	public function authorize($dase,$collection_ascii_id,$eid) {
		//the checkAuth methods use the collection manager
		//table to look-up privilege level
		$dase->user = new Dase_User();
		if ($dase->user->eid == $eid &&
			$dase->user->checkAuth($collection_ascii_id,'read')) {
				return true;
			}
		return false;
	}
}

