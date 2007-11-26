<?php

$t = new Dase_Xslt(XSLT_PATH.'search/result.xsl');
$t->set('local-layout',XSLT_PATH.'search/result.xml');
$t->set('src',APP_ROOT.'/atom/'. $request_url . '?' . $query_string);
Dase::display($t->transform());

