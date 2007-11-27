<?php

$u = Dase_User::get($params['eid']);
$tag = new Dase_DB_Tag;
$tag->dase_user_id = $u->id;
$tag->tag_type_id = CART;
$tag->findOne();
$t = new Dase_Xslt(XSLT_PATH.'search/result.xsl');
$t->set('local-layout',XSLT_PATH.'search/result.xml');
$t->set('src',APP_ROOT.'/atom/user/'.$u->eid.'/tag/id/'.$tag->id);
Dase::display($t->transform());

