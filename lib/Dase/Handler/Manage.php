<?php

class Dase_Handler_Manage extends Dase_Handler
{
	public $resource_map = array(
		'phpinfo' => 'phpinfo',
		'colors' => 'colors',
		'manager/email' => 'manager_email',
		'eid/{eid}' => 'ut_person',
		'name/{lastname}' => 'ut_person',
		'docs' => 'docs',
		'db/indexes' => 'db_indexes',
		'schema/{type}' => 'schema',
		'users' => 'users',
		'user/{eid}' => 'user',
		'collection/form' => 'collection_form',
		'ingest/checker' => 'ingest_checker',
		'ingester' => 'ingester',
		'ingest/form' => 'ingest_form',
		'collections' => 'collections',
		'settings' => 'settings',
		'status' => 'status',
		'/' => 'index',
	);

	public function setup($r)
	{
		//all routes here require superuser privileges
		$user = $r->getUser();
		if (!$user->isSuperuser()) {
			$r->renderError(401);
		}
	}

	public function getSettings($r)
	{
		$r->renderResponse("hello from settings");
	}

	public function getStatus($r)
	{
		$r->renderResponse("hello from status");
	}

	public function getIndex($r)
	{
		$tpl = new Dase_Template($r);
		$r->renderResponse($tpl->fetch('manage/layout.tpl'));
	}

	public function getCollections($r)
	{
		$tpl = new Dase_Template($r);
		$r->renderResponse($tpl->fetch('manage/collections.tpl'));
	}

	public function getPhpinfo($r)
	{
		phpinfo();
		exit;
	}

	public function getUsersJson($r)
	{
		if ($r->has('limit')) {
			$limit = $r->get('limit');
		} else {
			$limit = 100;
		}
		$r->renderResponse(Dase_DBO_DaseUser::listAsJson($limit));
	}

	public function getUsers($r)
	{
		if ($r->has('limit')) {
			$limit = $r->get('limit');
		} else {
			$limit = 100;
		}
		$tpl = new Dase_Template($r);
		$tpl->assign('limit',$limit);
		$r->renderResponse($tpl->fetch('manage/users.tpl'));
	}

	public function getUser($r)
	{
		echo $r->get('eid');exit;
		//$tpl = new Dase_Template($r);
		//$r->renderResponse($tpl->fetch('manage/users.tpl'));
	}

	public function getColors($r) 
	{
		$tpl = new Dase_Template($r);
		$r->renderResponse($tpl->fetch('manage/palette.tpl'));
	}

	public function getDbIndexes($r) 
	{
		$tpl = new Dase_Template($r);
		$tpl->assign('indexes',Dase_DB::listIndexes());
		$r->renderResponse($tpl->fetch('manage/db_indexes.tpl'));
	}

	public function getSchema($r)
	{
		switch ($r->get('type')) {
		case 'sqlite': 
			$types['sqlite']['bigint'] = 'INTEGER';
			$types['sqlite']['boolean'] = 'INTEGER';
			$types['sqlite']['character varying'] = "TEXT";
			$types['sqlite']['double precision'] = "REAL";
			$types['sqlite']['double'] = "REAL";
			$types['sqlite']['integer'] = 'INTEGER';
			$types['sqlite']['int'] = 'INTEGER';
			$types['sqlite']['mediumtext'] = "TEXT";
			$types['sqlite']['text'] = "TEXT";
			$types['sqlite']['tinyint'] = 'INTEGER';
			$types['sqlite']['varchar'] = "TEXT";
			$r->renderResponse('sorry');
			break;
		case 'xml':
			$r->response_mime_type = 'text/xml';
			$r->renderResponse(Dase_DB::getSchemaXml());
			break;
		case 'mysql':
			$target_db = 'mysql';
			$schema_xml = Dase_DB::getSchemaXml();
			$sx = simplexml_load_string($schema_xml);
			$out = '';

			$types['mysql']['bigint'] = 'int';
			$types['mysql']['boolean'] = 'tinyint';
			$types['mysql']['tinyint'] = 'tinyint';
			$types['mysql']['character varying'] = "varchar";
			$types['mysql']['varchar'] = "varchar";
			$types['mysql']['double precision'] = "REAL";
			$types['mysql']['double'] = "REAL";
			$types['mysql']['integer'] = 'int';
			$types['mysql']['int'] = 'int';
			$types['mysql']['mediumtext'] = "text";
			$types['mysql']['text'] = "text";

			foreach ($sx->table as $table) {
				if ($r->has('prefix')) {
					//todo: figure out implementing table prefixes in config as well
					//$table['name'] = $r->get('prefix').'_'.$table['name'];
				}
				$out .= "DROP TABLE IF EXISTS `{$table['name']}`;\n";
				$out .= "CREATE TABLE `{$table['name']}` (\n";
				$sql = '';
				foreach ($table->column as $col) {
					if ('true' == $col['is_primary_key']) {
						$id = "`id` int(11) NOT NULL auto_increment,\n";
						$pk = "PRIMARY KEY (`{$col['name']}`)\n";  
					} else {
						$col_type = (string) $col['type'];
						$sql .= "`{$col['name']}`"  . " " . $types[$target_db][$col_type];  
						if ($col['max_length']) {
							$sql .= "({$col['max_length']})";
						} else {
							if ('int' == $types[$target_db][$col_type]) {
								$sql .= "(11)";
							}
							if ('tinyint' == $types[$target_db][$col_type]) {
								$sql .= "(1)";
							}

						}
						$sql .= " default NULL,\n";  
					}
				}
				$out .= $id;
				$out .= $sql;
				$out .= $pk;
				$out .= ") ENGINE=MyISAM DEFAULT CHARSET=utf8;\n\n\n";
			}
			$r->response_mime_type = 'text/plain';
			$r->renderResponse($out);
			break;
		default:
			$r->renderResponse(Dase_DB::getSchemaXml());
		}
	}

	public function getManagerEmail($r) 
	{
		$cms = new Dase_DBO_CollectionManager;
		foreach ($cms->find() as $cm) {
			if ('none' != $cm->auth_level) {
				$person = Utlookup::getRecord($cm->dase_user_eid);
				if (isset($person['email'])) {
					$managers[] = $person['name']." <".$person['email'].">"; 
				}
			}
		}
		$r->response_mime_type = 'text/plain';
		$r->renderResponse(join("\n",array_unique($managers)));
	}

	public function getUtPerson($r) 
	{
		if ($r->has('lastname')) {
			$person = Utlookup::lookup($r->get('lastname'),'sn');
		} else {
			$person = Utlookup::getRecord($r->get('eid'));
		}
		$r->response_mime_type = 'text/plain';
		$r->renderResponse(var_export($person,true));
	}

	public function getDocs($r)
	{
		// note: doc comments are only displayed
		// on first web view after a file is updated,
		// indicating that a bytecode cache is removing comments

		$tpl = new Dase_Template($r);
		$dir = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(DASE_PATH.'/lib'));
		foreach ($dir as $file) {
			$matches = array();
			if ( 
				false === strpos($file->getPathname(),'smarty') &&
				false === strpos($file->getPathname(),'Smarty') &&
				false === strpos($file->getPathname(),'getid3') &&
				false === strpos($file->getPathname(),'htaccess') &&
				$file->isFile()
			) {
				try {
					$filepath = $file->getPathname();
					include_once $filepath;
				} catch(Exception $e) {
					print $e->getMessage() . "\n";
				}
			}
		}
		$arr = get_declared_classes();
		natcasesort($arr);
		//include only
		$filter = create_function('$filename', 'return preg_match("/(dase|mimeparse|service|utlookup)/i",$filename);');
		$class_list = array_filter($arr,$filter);
		//except
		$filter = create_function('$filename', 'return !preg_match("/autogen/i",$filename);');
		$class_list = array_filter($class_list,$filter);
		$tpl->assign('class_list',$class_list);
		if ($r->has('class_id')) {
			$tpl->assign('phpversion',phpversion()); 
			$tpl->assign('class_id',$r->get('class_id')); 
			$documenter = new Documenter($class_list[$r->get('class_id')]);
			$tpl->assign('default_properties',$documenter->getDefaultProperties());
			$tpl->assign('doc',$documenter);
		}
		$r->renderResponse($tpl->fetch('manage/docs.tpl'));
	}

	public function getCollectionForm($r)
	{
		$tpl = new Dase_Template($r);
		$r->renderResponse($tpl->fetch('manage/collection_form.tpl'));
	}

	public function postToIngestChecker($r)
	{
		$url = $r->get('url');
		$feed = Dase_Atom_Feed::retrieve($url.'?format=atom');
		$count = $feed->getItemCount();
		$title = $feed->getTitle();
		if ($title) {
			$r->renderResponse('ok|'.$count.'|'.$title);
		} else {
			$r->renderResponse('no');
		}
	}

	public function postToIngester($r)
	{
		$url = $r->get('url');
		$feed = Dase_Atom_Feed::retrieve($url.'?format=atom');
		$coll_ascii_id = $feed->getAsciiId();
		$feed->ingest($r);
		$cm = new Dase_DBO_CollectionManager;
		$cm->dase_user_eid = $r->getUser()->eid;
		$cm->collection_ascii_id = $coll_ascii_id;
		$cm->auth_level = 'superuser';
		$cm->created = date(DATE_ATOM); 
		$cm->insert();
		$r->getUser()->expireDataCache();
		$r->renderResponse('completed operation');
	}

	public function getIngestForm($r)
	{
		$tpl = new Dase_Template($r);
		$r->renderResponse($tpl->fetch('manage/ingest_form.tpl'));
	}
}

