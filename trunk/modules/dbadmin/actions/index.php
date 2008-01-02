<?php
require(MODULE_PATH . '/lib/Smarty/Smarty.class.php');
$tpl = new Smarty();
$tpl->template_dir = MODULE_PATH . '/templates';
$tpl->compile_id = 'dbadmin';
$tpl->compile_dir = CACHE_DIR;
$tpl->assign('module_root',MODULE_ROOT);

$types['sqlite'] = "SQLite";
$types['mysql'] = "MySQL";
$types['pgsql'] = "PostgreSQL";

$db_type = Dase_DB::getDbType();

foreach (Dase_DB::listTables() as $t) {
	$tables[$t][] = 'id';
	foreach (Dase_DB::listColumns($t) as $c) {
		if ('id' != $c) {
			$tables[$t][] = $c;
		}
	}
}

$tpl->assign('tables',$tables);
$tpl->assign('db',$types[$db_type]);
$tpl->display('index.tpl');

