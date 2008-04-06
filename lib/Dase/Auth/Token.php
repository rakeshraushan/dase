<?php

class Dase_Auth_Token
{
	public function authorize($params)
	{
		if (Dase_Filter::filterGet('token') == md5(Dase::getConfig('token'))) {
			return true;
		}
		return false;
	}
}

