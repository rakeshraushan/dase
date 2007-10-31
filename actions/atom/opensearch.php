<?php
$search = new Dase_Search($params);

$tpl = new Dase_Xml_Template;
$tpl->setXml($search->getOpenSearchResult());
$tpl->setContentType('application/atom+xml');
$tpl->display();
