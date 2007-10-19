<?php

$search = new Dase_Search();
$item_ids = $search->getItems();

$start = 0;
$max_items = 50;
$id_string = join(',',array_slice($item_ids,$start,$max_items));
/* still neeed to do collection specific, echo, etc.
 */
$t = new Dase_Xslt(XSLT_PATH.'search_result.xsl');
$t->set('layout',XSLT_PATH.'search_result.xml');
$t->set('items',APP_ROOT.'/xml/item/thumbs/' . $id_string);
$tpl = new Dase_Html_Template();
$tpl->setText($t->transform());
$tpl->display();

