<?php

require_once 'Dase/DB/Autogen/Tag.php';

class Dase_DB_Tag extends Dase_DB_Autogen_Tag 
{
	public static function getByUser($user) {
		$tag = new Dase_DB_Tag;
		$tag->dase_user_id = $user->id;
		return $tag->findAll();
	}
}
