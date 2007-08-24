<?php

$coll = Dase_DB_Collection::get('efossils_collection');
$page_name = $params['page_name'];


$page['student']['title'] = "STUDENT > activities";
$page['teacher']['title'] = "TEACHER > resources";
$page['comparison']['title'] = "COMPARISON PHOTOS";

$tpl = Dase_Template::instance('elucy');
$tpl->assign('page_name',$page_name);
$tpl->assign('page_title',$page[$page_name]['title']);
$tpl->display('second.tpl');

