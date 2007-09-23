<?php
$c = new Dase_Xml_Collection($collection_ascii_id);
$keywords = $c->getAttVals('keyword');
asort($keywords);

$tpl = Dase_Template::instance('friesen');
$tpl->assign('keywords',$keywords);
$tpl->display('view_search_items.tpl');

