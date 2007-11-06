<?php
$routes = Dase::compileRoutesXslt();

$tpl = Dase_Template::instance();
$tpl->assign('breadcrumb_url','manage/routes');
$tpl->assign('breadcrumb_name','route mappings');
$tpl->assign('routes',$routes);
$tpl->display('manage/index.tpl');
exit;
