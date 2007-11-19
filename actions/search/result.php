<?php

$t = new Dase_Xslt(XSLT_PATH.'search/result.xsl');
$t->set('local-layout',XSLT_PATH.'search/result.xml');
//$t->set('atom','http://www.tbray.org/ongoing/ongoing.atom');
$t->set('atom',APP_ROOT.'/atom/'. $request_url . '?' . $query_string);
$tpl = new Dase_Html_Template();
$tpl->setText($t->transform());
$tpl->display();

