<?php

$t = new Dase_Xslt(XSLT_PATH.'item/transform.xsl');
$t->set('local-layout',XSLT_PATH.'item/source.xml');
$t->set('src',APP_ROOT.'/atom/'. $request_url . '?' . $query_string);
Dase::display($t->transform());

