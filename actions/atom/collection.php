<?php

if (isset($params['collection_ascii_id'])) {
	$tpl = new Dase_Xml_Template;
	$tpl->setXml(Dase_Collection::getAtom($params['collection_ascii_id'],'xml'));
	$tpl->display();
}
