<?php

require_once 'Dase/DBO/Autogen/Subscription.php';

class Dase_DBO_Subscription extends Dase_DBO_Autogen_Subscription 
{
	function getUser()
	{
		$user = new Dase_DBO_DaseUser;
		$user->load($this->dase_user_id);
		return $user;
	}

	function getTag()
	{
		$tag = new Dase_DBO_Tag;
		$tag->load($this->tag_id);
		return $tag;
	}
}
