<?php

if (isset($params['eid'])) {
	$u = Dase_User::get($params['eid']);
	$tags = new Dase_DB_Tag;
	$tags->dase_user_id = $u->id;
	$page = $tags->findAsXml(true);
}
if ($page) {
	Dase::display($page);
} else {
	Dase::error(404);
}
