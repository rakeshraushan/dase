<?php
if (Dase::filterGet('all')) {
	$public_only = 0;
} else {
	$public_only = 1;
}
$t = new Dase_Xslt(XSLT_PATH.'/atom/collections.xsl',XSLT_PATH.'/atom/layout.xml');
$t->set('src',APP_ROOT.'/xml/');
$tpl = new Dase_Xml_Template();
$tpl->setXml($t->transform());
$tpl->setContentType('application/atom+xml');
$tpl->display();

