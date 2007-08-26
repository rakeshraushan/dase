<?php

$coll = Dase_DB_Collection::get('efossils_collection');
$page_name = 'comparison';


$page_title = "COMPARISON PHOTOS";

$tpl = Dase_Template::instance('elucy');
$tpl->assign('page_name',$page_name);
$tpl->assign('page_title',$page_title);
$tpl->display('comparison.tpl');

