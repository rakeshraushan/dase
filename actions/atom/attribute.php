<?php
$t = new Dase_Xslt(XSLT_PATH.'/atom/attribute.xsl',XSLT_PATH.'/atom/layout.xml');
$xml_request_url = str_replace('atom/','xml/',$request_url);
$t->set('src',APP_ROOT. '/' . $xml_request_url . '?' . $query_string);
Dase::display($t->transform());
