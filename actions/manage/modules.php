<?php
$tpl = new Smarty;

include(DASE_CONFIG);
$dir = (DASE_PATH . "/modules");
foreach (new DirectoryIterator($dir) as $file) {
	if ($file->isDir() && !$file->isDot()) {
		$module = $file->getFilename();
		$modules[$file->getFilename()] = 'installed';
	}
}
$tpl->assign('app_root',APP_ROOT);
$tpl->assign('modules',$modules);
$tpl->assign('breadcrumb_url','manage/modules');
$tpl->assign('breadcrumb_name','modules');
$tpl->display('manage/index.tpl');
exit;
