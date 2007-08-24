<?php
$tpl = Dase_Template::instance();

include(DASE_CONFIG);
$dir = (DASE_PATH . "/modules");
foreach (new DirectoryIterator($dir) as $file) {
	if ($file->isDir() && !$file->isDot()) {
		$module = $file->getFilename();
		//need to validate routes.xml as well
		if (is_file("$dir/$module/inc/routes.xml") && in_array($module,$conf['modules'])) {
			$modules[$file->getFilename()] = 'active';
		} else {
			$modules[$file->getFilename()] = 'not active';
		}
	}
}
$tpl->assign('modules',$modules);
$tpl->assign('breadcrumb_url','manage/modules');
$tpl->assign('breadcrumb_name','modules');
$tpl->display('manage/index.tpl');
exit;
