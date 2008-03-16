<?php

require_once 'Dase/DBO/Autogen/CollectionManager.php';

class Dase_DBO_CollectionManager extends Dase_DBO_Autogen_CollectionManager 
{
	public $name;

	public function getUser()
	{
		$user = new Dase_DBO_DaseUser;
		$user->eid = $this->dase_user_eid;
		return	$user->findOne();
	}
}
