<?php

$t = new Dase_Xslt(XSLT_PATH.'/xoxo/xml2xoxo.xsl',XSLT_PATH.'/xoxo/xoxo.xml');
$t->set('src',APP_ROOT.'/xml');
$tpl = new Dase_Html_Template();
$tpl->setText($t->transform());
$tpl->display();
