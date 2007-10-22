<?php

$t = new Dase_Xslt(XSLT_PATH.'login/form.xsl');
$t->set('local-layout',XSLT_PATH.'login/form.xml');
$tpl = new Dase_Html_Template();
$tpl->setText($t->transform());
$tpl->display();
