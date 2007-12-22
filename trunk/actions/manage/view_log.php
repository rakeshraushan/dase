<?php
if (isset($params['log_name'])) {
	switch ($params['log_name']) {
	case 'standard':
		$file = 'standard.log';
		break;
	case 'error':
		$file = 'error.log';
		break;
	case 'sql':
		$file = 'sql.log';
		break;
	case 'remote':
		$file = 'remote.log';
		break;
	default:
		header("HTTP/1.0 404 Not Found");
		exit;
	}
	$log = file_get_contents(DASE_PATH . "/log/" . $file);
}
$tpl = new Smarty;
$tpl->assign('app_root',APP_ROOT);
$tpl->assign('breadcrumb_url',"manage/log/{$params['log_name']}");
$tpl->assign('breadcrumb_name',"{$params['log_name']} log");
$tpl->assign('log',$log);
$tpl->assign('log_name',$params['log_name']);
$tpl->display('manage/index.tpl');
exit;
