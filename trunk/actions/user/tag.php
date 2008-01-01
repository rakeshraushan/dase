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

$t = new Dase_Xslt(XSLT_PATH.'item_set/transform.xsl');
$t->set('local-layout',XSLT_PATH.'item_set/source.xml');
//THIS script is protected by eid auth, but how to protect restricted
//atom and xml documents that feed it? DASe requests AND serves the docs
//so we can hash a secret in the url and read that for the 'token' auth (see Dase.php)
$t->set('src',APP_ROOT.'/atom/user/'.$u->eid.'/tag/id/'.$tag->id.'?token='.md5(Dase::getConf('token')));
//print(APP_ROOT.'/atom/user/'.$u->eid.'/tag/id/'.$tag->id.'?token='.md5(Dase::getConf('token')));exit;
Dase::display($t->transform());


