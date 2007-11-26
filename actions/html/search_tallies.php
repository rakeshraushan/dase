<?php

$t = new Dase_Xslt(XSLT_PATH.'/xoxo/search_tallies.xsl',XSLT_PATH.'/xoxo/search_tallies.xml');
$xml_request_url = str_replace('html/tallies/','xml/',$request_url);
$t->set('src',APP_ROOT. '/' . $xml_request_url . '?' . $query_string);
Dase::display($t->transform());

