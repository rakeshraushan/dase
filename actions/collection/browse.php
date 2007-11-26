<?php

$t = new Dase_Xslt(XSLT_PATH.'collection/browse.xsl');
$t->set('local-layout',XSLT_PATH.'collection/browse.xml');
$t->set('src',APP_ROOT. '/xml/' . $request_url . '?' . $query_string);
Dase::display($t->transform());
