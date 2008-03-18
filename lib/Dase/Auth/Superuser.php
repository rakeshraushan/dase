<?php

class Dase_Auth_Superuser
{
	public function authorize($params)
	{
		//is this as secure as it needs to be?
		//(susceptible to replay attack)
		$user = new Dase_User();
		if (in_array($user->eid,Dase_Config::get('superuser'))) {
			return true;
		}
		return false;
	}

}

