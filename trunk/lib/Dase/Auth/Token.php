<?php

class Dase_Auth_Token
{
	public function authorize($dase,$collection_ascii_id='',$eid='') {
			if (Dase::filterGet('token') == md5(Dase::getConf('token'))) {
				return true;
			}
			return false;
	}
}

