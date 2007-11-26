<?php

$t = new Dase_Xslt(XSLT_PATH.'login/form.xsl');
$t->set('local-layout',XSLT_PATH.'login/form.xml');
Dase::display($t->transform());
