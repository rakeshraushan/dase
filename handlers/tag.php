<?php

class TagHandler
{
	public static function asAtom($params)
	{
		$u = Dase_User::get($params);
		$tag = new Dase_DBO_Tag;
		if (isset($params['id'])) {
			$tag->load($params['id']);
			if ($tag->dase_user_id != $u->id) {
				Dase_Error::report(401);
			}
		} elseif (isset($params['ascii_id'])) {
			$tag->ascii_id = $params['ascii_id'];
			$tag->dase_user_id = $u->id;
			if (!$tag->findOne()) {
				Dase_Error::report(401);
			}
		} else {
			Dase_Error::report(404);
		}
		Dase::display($tag->asAtom());
	}
}

