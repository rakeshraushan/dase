<?php

$t = new Dase_Xslt(XSLT_PATH.'auth/logoff.xsl',XSLT_PATH.'auth/auth.xml');
Dase::display($t->transform(),false);
