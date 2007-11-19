<?php

//smarty version: 41 req/sec
//xslt version: 103 req/sec
$t = new Dase_Xslt(XSLT_PATH.'collection/list.xsl');
$t->set('local-layout',XSLT_PATH.'collection/list.xml');
$t->set('collections',APP_ROOT.'/xml');
$tpl = new Dase_Html_Template();

//xhtml output:
$tpl->setText($t->transform());
$tpl->display();

//html output:
//$t2 = new Dase_Xslt(XSLT_PATH.'xhtml2html.xsl',$t->transform());
//$tpl = new Dase_Html_Template();
//$tpl->setText($t2->transform());
//$tpl->display();

