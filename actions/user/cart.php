<?php

$u = Dase_User::get($params['eid']);
$tag = new Dase_DB_Tag;
$tag->dase_user_id = $u->id;
$tag->tag_type_id = CART;
$tag->findOne();
$t = new Dase_Xslt(XSLT_PATH.'search/result.xsl');
$t->set('local-layout',XSLT_PATH.'search/result.xml');

//THIS script is protected by eid auth, but how to protect restricted
//atom and xml documents that feed it? DASe requests AND serves the docs
//so we can hash a secret in the url and read that for the 'token' auth (see Dase.php)
$t->set('src',APP_ROOT.'/atom/user/'.$u->eid.'/tag/id/'.$tag->id.'?token='.md5(Dase::getConf('token')));
Dase::display($t->transform());

