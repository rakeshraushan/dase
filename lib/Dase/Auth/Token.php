<?php

class Dase_Auth_Token
{
	public function authorize($params)
	{
		if (Dase::filterGet('token') == md5(Dase::getConf('token'))) {
			return true;
		}
		return false;
	}
}

