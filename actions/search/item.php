<?php

$t = new Dase_Xslt(XSLT_PATH.'item/default.xsl');
$t->set('local-layout',XSLT_PATH.'item/default.xml');
$t->set('src',APP_ROOT.'/atom/'. $request_url . '?' . $query_string);
Dase::display($t->transform());

