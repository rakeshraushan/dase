<?php

class Dase_Auth_Eid
{
	public function authorize($params,$type)
	{
		if (isset($params['eid']) && $params['eid']) {
			$user = new Dase_User();
			if ($user->eid == $params['eid']) {
				return true;
			}
		}
		return false;
	}
}

