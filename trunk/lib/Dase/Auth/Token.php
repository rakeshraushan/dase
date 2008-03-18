<?php

class Dase_Auth_Token
{
	public function authorize($params)
	{
		if (Dase_Filter::filterGet('token') == md5(Dase_Config::get('token'))) {
			return true;
		}
		return false;
	}
}

