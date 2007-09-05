<?php

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


$tpl = Dase_Template::instance('dbadmin');
$tpl->assign('tables',$tables);
$tpl->assign('db',$types[$db_type]);
$tpl->display('index.tpl');

