<?php

$coll = Dase_DB_Collection::get('efossils_collection');
$page_name = 'student';
$page_title = "STUDENT > activities";

$tpl = Dase_Template::instance('elucy');
$tpl->assign('page_name',$page_name);
$tpl->assign('page_title',$page_title);
$tpl->display('student.tpl');

