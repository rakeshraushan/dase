<?php
$u = Dase_User::get($params['eid']);
$tag = new Dase_DB_Tag;
if (isset($params['id'])) {
	$tag->load($params['id']);
	if ($tag->dase_user_id != $u->id) {
		Dase::error(401);
	}
} elseif (isset($params['ascii_id'])) {
	$tag->ascii_id = $params['ascii_id'];
	$tag->dase_user_id = $u->id;
	if (!$tag->findOne()) {
		Dase::error(401);
	}
} else {
	Dase::error(404);
}
Dase::display($tag->asAtom());

