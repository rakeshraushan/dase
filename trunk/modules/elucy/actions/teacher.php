<?php

$coll = Dase_DB_Collection::get('efossils_collection');
$page_name = 'teacher';
$page_title = "TEACHER > resources";

$tpl = Dase_Template::instance('elucy');
$tpl->assign('page_name',$page_name);
$tpl->assign('page_title',$page_title);
$tpl->display('teacher.tpl');

