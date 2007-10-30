<?php

$search = new Dase_Search($params);
$result = $search->getResult();
$start = Dase::filterGet('start');
$max = Dase::filterGet('max');


$link = $result['link'];

$start = $start ? $start : 0;
$max = $max ? $max : 30;
$total = count($result['item_ids']);

$next_start = $start + $max;
$prev_start = $start - $max;
if ($prev_start < 0) {
	$prev_start = 0;
}

if (1 == count($result['item_ids'])) {

}

//result includes: tallies,item_ids,count,hash,search,sql,echo

$sx = new SimpleXMLElement('<search/>');
$tallies = $sx->addChild('tallies');
foreach($result['tallies'] as $cname => $total) {
	$tal = $tallies->addChild('tally');
	$tal->addAttribute('collection_name',$cname);
	$tal->addAttribute('total',$total);
}
$sx->addChild('total',$result['count']);
$sx->addChild('sql',$result['sql']);
$sx->addChild('prev',$result['link'] . "&amp;start=" . $prev_start);
$sx->addChild('next',$result['link'] . "&amp;start=" . $next_start);
//$sx->addChild('search',var_export($result['search'],true));
$sx->addChild('echo',var_export($result['echo'],true));

if (Dase::instance()->collection) {
	$c = Dase::instance()->collection;
	$coll = $sx->addChild('collection');
	$coll->addAttribute('name',$c->collection_name);
	$coll->addAttribute('ascii_id',$c->ascii_id);
}

$id_string = join(',',array_slice($result['item_ids'],$start,$max));

/* still need to do collection specific, echo, etc.
 */
$t = new Dase_Xslt(XSLT_PATH.'search/result.xsl');
$t->addSourceNode($sx);
$t->set('local-layout',XSLT_PATH.'search/result.xml');
$t->set('items',APP_ROOT.'/xml/item/thumbs/' . $id_string);
$t->set('total',$total);
$tpl = new Dase_Html_Template();
$tpl->setText($t->transform());
$tpl->display();

