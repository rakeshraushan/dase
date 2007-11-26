<?php

$t = new Dase_Xslt(XSLT_PATH.'/xoxo/xml2xoxo.xsl',XSLT_PATH.'/xoxo/xoxo.xml');
$xml_request_url = str_replace('html/','xml/',$request_url);
$t->set('src',APP_ROOT. '/' . $xml_request_url . '?' . $query_string);
Dase::display($t->transform());
