<?php

class Dase_ModuleHandler_Dbadmin extends Dase_Handler {

	public $resource_map = array(
		'/' => 'info',
		'index' => 'info',
		'index/{msg}' => 'info',
	);

	public function setup($request)
	{
	}

	public function getInfo($request) 
	{
		$tpl = new Dase_Template($request,true);

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
		$request->renderResponse($tpl->fetch('index.tpl'));
	}
}
