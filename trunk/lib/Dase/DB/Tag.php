<?php

require_once 'Dase/DB/Autogen/Tag.php';

class Dase_DB_Tag extends Dase_DB_Autogen_Tag 
{
	public static function getByUser($user) {
		$tag = new Dase_DB_Tag;
		/* this is not noticeably faster
		 * than my ORM
		$sql = "
			SELECT *
			FROM tag
			WHERE dase_user_id = ?
			";
		$db = Dase_DB::get();
		$sth = $db->prepare($sql);
		$sth->setFetchMode(PDO::FETCH_ASSOC);
		$sth->execute(array($user->id));
		return $sth->fetchAll();
		 */
		$tag->dase_user_id = $user->id;
		return $tag->findAll();
	}
}
