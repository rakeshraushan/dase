<?php

$c = Dase_DB_Collection::get($params['collection_ascii_id']);

$tpl = Dase_Template::instance();
$tpl->assign('content','search');
$tpl->assign('collection',$c);
$tpl->assign('attributes',$c->getAttributes('attribute_name'));
$tpl->display('page.tpl');
