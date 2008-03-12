<?php

class Dase_Auth_None
{
	public function authorize($dase,$collection_ascii_id='',$eid='') {
		return true;
	}

}

