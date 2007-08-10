<?php

//add hook here?

$att = new Dase_DB_Attribute;
$att->collection_id = Dase_DB_Object::getId('collection','vrc_collection');
foreach ($att->findAll() as $a) {
	print "{$a['attribute_name']}\n";
}

$tpl = Dase_Template::instance();
$tpl->assign('content','search');
$tpl->display('page.tpl');
