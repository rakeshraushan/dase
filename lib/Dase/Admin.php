<?php

class Dase_Admin 
{

	public static function getAcl() 
	{
		$acl = array();
		$cms = new Dase_DBO_CollectionManager;
		foreach ($cms->find() as $cm) {
			$cm->dase_user_eid = strtolower($cm->dase_user_eid);
			$acl['collections'][$cm->collection_ascii_id][$cm->dase_user_eid] = $cm->auth_level;
		}
		$users = new Dase_DBO_DaseUser;
		foreach ($users->find() as $user) {
			$user->eid = strtolower($user->eid);
			$acl['users'][$user->eid] = $user->id;
		}
		/*
		$tags = new Dase_DBO_Tag;
		foreach ($tags->find() as $tag) {
			if ($tag->getUser()) {
				$eid = strtolower($tag->user->eid);
			} else {
				$eid = 'no eid on record';
			}
			$acl['tags'][$tag->ascii_id][$eid] = $tag->id;
		}
		 */
		return $acl;
	}
}

