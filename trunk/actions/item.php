<?php

if (isset($params['collection_ascii_id']) && ($params['serial_number'])) {
	$c = Dase_DB_Collection::get($params['collection_ascii_id']);
	$item = new Dase_DB_Item;
	$item->collection_id = $c->id;
	$item->serial_number = $params['serial_number'];
	$item->findOne();
	$item->collection = $c;
	$item->getValues();
	$item->getViewitem();
	$tpl = Dase_Template::instance();
	$tpl->assign('collection',$c);
	$tpl->assign('item',$item);
	$tpl->assign('content','item');
	$tpl->display('page.tpl');
	exit;
}
