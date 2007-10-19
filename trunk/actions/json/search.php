<?php

$search = new Dase_Search();

$js = new Dase_Json;
$items_json = "var items_json = " . $js->encodeData($search->getItems());

print($items_json);exit;

/* still neeed to do collection specific, echo, etc.
 */

$t = new Dase_Xslt(XSLT_PATH.'search_result.xsl');
$t->set('layout',XSLT_PATH.'search_result.xml');
$t->set('items_json',$items_json);
$tpl = new Dase_Html_Template();
$tpl->setText($t->transform());
$tpl->display();

