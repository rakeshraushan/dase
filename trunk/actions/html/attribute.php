<?php

$t = new Dase_Xslt(XSLT_PATH.'/xoxo/xml2xoxo.xsl',XSLT_PATH.'/xoxo/xoxo.xml');
$t->set('src',APP_ROOT. '/xml/' . $request_url . '?' . $query_string);
Dase::display($t->transform());
