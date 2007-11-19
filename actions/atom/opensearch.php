<?php

//implement filecache here!

$t = new Dase_Xslt(XSLT_PATH.'/atom/opensearch.xsl',XSLT_PATH.'/atom/layout.xml');
$t->set('request',APP_ROOT. '/' . $request_url . '?' . $query_string);
$request_url = str_replace('atom/','xml/',$request_url);
$t->set('src',APP_ROOT. '/' . $request_url . '?' . $query_string);
$tpl = new Dase_Xml_Template();
$tpl->setXml($t->transform());
$tpl->setContentType('application/atom+xml');
$tpl->display();

