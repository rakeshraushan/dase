<?php

$t = new Dase_Xslt(XSLT_PATH.'collection/list.xsl');
$t->set('src',APP_ROOT. '/atom');

//xhtml output:
Dase::display($t->transform());

//html output:
//$t2 = new Dase_Xslt(XSLT_PATH.'xhtml2html.xsl',$t->transform());
//Dase::display($t2->transform());

