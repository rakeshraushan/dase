<?php
$t = new Dase_Xslt(
	XSLT_PATH.'manage/common.xsl',
	XSLT_PATH.'manage/source.xml'
);
Dase::display($t->transform());







