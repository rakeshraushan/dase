<?php
$routes = Dase::compileRoutes();

$tpl = new Smarty;
$tpl->assign('app_root',APP_ROOT);
$tpl->assign('breadcrumb_url','manage/routes');
$tpl->assign('breadcrumb_name','route mappings');
$tpl->assign('routes',$routes);
$tpl->display('manage/index.tpl');
exit;
