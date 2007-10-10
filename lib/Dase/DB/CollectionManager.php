<?php

require_once 'Dase/DB/Autogen/CollectionManager.php';

class Dase_DB_CollectionManager extends Dase_DB_Autogen_CollectionManager 
{
	public function getUser() {
		$user = new Dase_DB_DaseUser;
		$user->eid = $this->dase_user_eid;
		return	$user->findOne();
	}
}
