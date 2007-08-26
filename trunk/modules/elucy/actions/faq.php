<?php

$coll = Dase_DB_Collection::get('efossils_collection');

$sx = new SimpleXMLElement($coll->getItemsXmlByType('faq'));
$definitions = array();
foreach ($sx->item as $item) {
	list($term) = $item->xpath("metadata[@attribute_ascii_id='faq_question']");
	list($def) =$item->xpath("metadata[@attribute_ascii_id='faq_answer']");
	if ($term && $def) {
		$definitions[(string) $term] = $def;
	}
}

function cmp($a, $b)
{
		    return strnatcasecmp($a, $b);
}
uksort($definitions, "cmp");


$tpl = Dase_Template::instance('elucy');
$tpl->assign('definitions',$definitions);

$tpl->display('faq.tpl');
