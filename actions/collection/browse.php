<?php

$t = new Dase_Xslt(XSLT_PATH.'collection/browse.xsl');
$t->set('local-layout',XSLT_PATH.'collection/browse.xml');
$t->set('src',APP_ROOT. '/atom/' . $params['collection_ascii_id']);
Dase::display($t->transform());
