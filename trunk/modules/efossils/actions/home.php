<?php

$coll = Dase_DB_Collection::get('efossils_collection');
	//$sx = new SimpleXMLElement($i->getXml());


print_r($coll->getItemsXmlByAttVal('resource_uri','/home'));

exit;

$tpl = Dase_Template::instance('efossils');
$tpl->display('home.tpl');

