<?php

$coll = Dase_DB_Collection::get('efossils_collection');
//$sx = new SimpleXMLElement($i->getXml());

$sx = new SimpleXMLElement($coll->getItemsXmlByType('glossary'));
//print_r($coll->getItemsXmlByType('glossary'));
//exit;
$definitions = array();
foreach ($sx->item as $item) {
	list($term) = $item->xpath("metadata[@attribute_ascii_id='glossary_term']");
	list($def) =$item->xpath("metadata[@attribute_ascii_id='glossary_definition']");
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

$tpl->display('glossary.tpl');
