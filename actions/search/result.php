<?php

$search = new Dase_Search();
$result = $search->getResult();

//result includes: tallies,item_ids,count,hash,search,sql

$sx = new SimpleXMLElement('<search/>');
$tallies = $sx->addChild('tallies');
foreach($result['tallies'] as $cname => $total) {
	$tal = $tallies->addChild('tally');
	$tal->addAttribute('collection_name',$cname);
	$tal->addAttribute('total',$total);
}
$sx->addChild('total',$result['count']);
$sx->addChild('sql',$result['sql']);
$sx->addChild('search',var_export($result['search'],true));

if (Dase::instance()->collection) {
	$c = Dase::instance()->collection;
	$coll = $sx->addChild('collection');
	$coll->addAttribute('name',$c->collection_name);
	$coll->addAttribute('ascii_id',$c->ascii_id);
}


$start = 0;
$max_items = 30;
$id_string = join(',',array_slice($result['item_ids'],$start,$max_items));

/* still need to do collection specific, echo, etc.
 */
$t = new Dase_Xslt(XSLT_PATH.'search/result.xsl');
$t->addSourceNode($sx);
$t->set('local-layout',XSLT_PATH.'search/result.xml');
$t->set('items',APP_ROOT.'/xml/item/thumbs/' . $id_string);
$tpl = new Dase_Html_Template();
$tpl->setText($t->transform());
$tpl->display();

