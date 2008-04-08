<?php

class Dase_Auth_Token
{
	public function authorize($params)
	{
		if (Dase_Filter::filterGet('token') == md5(Dase::getConfig('token').$params['eid'])) {
			//Dase::log('standard','token-based auth OK');
			return true;
		}
		return false;
	}
}

