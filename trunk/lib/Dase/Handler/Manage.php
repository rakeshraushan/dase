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
		'collections' => 'collections',
		'media/attributes' => 'media_attributes',
		'/' => 'index',
	);

	public function setup($request)
	{
		//all routes here require superuser privileges
		$user = $request->getUser();
		if (!$user->isSuperuser()) {
			$request->renderError(401);
		}
	}

	public function getIndex($request)
	{
		$tpl = new Dase_Template($request);
		$request->renderResponse($tpl->fetch('manage/layout.tpl'));
	}

	public function getCollections($request)
	{
		$tpl = new Dase_Template($request);
		$request->renderResponse($tpl->fetch('manage/collections.tpl'));
	}

	public function getPhpinfo($request)
	{
		phpinfo();
		exit;
	}

	public function getUsersJson($request)
	{
		if ($request->has('limit')) {
			$limit = $request->get('limit');
		} else {
			$limit = 100;
		}
		$request->renderResponse(Dase_DBO_DaseUser::listAsJson($limit));
	}

	public function getUsers($request)
	{
		if ($request->has('limit')) {
			$limit = $request->get('limit');
		} else {
			$limit = 100;
		}
		$tpl = new Dase_Template($request);
		$tpl->assign('limit',$limit);
		$request->renderResponse($tpl->fetch('manage/users.tpl'));
	}

	public function getUser($request)
	{
		echo $request->get('eid');exit;
		//$tpl = new Dase_Template($request);
		//$request->renderResponse($tpl->fetch('manage/users.tpl'));
	}

	public function getColors($request) 
	{
		$tpl = new Dase_Template($request);
		$request->renderResponse($tpl->fetch('manage/palette.tpl'));
	}

	public function getDbIndexes($request) 
	{
		$tpl = new Dase_Template($request);
		$tpl->assign('indexes',Dase_DB::listIndexes());
		$request->renderResponse($tpl->fetch('manage/db_indexes.tpl'));
	}

	public function getSchema($request)
	{
		switch ($request->get('type')) {
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
			$request->renderResponse('sorry');
			break;
		case 'xml':
			$request->response_mime_type = 'text/xml';
			$request->renderResponse(Dase_DB::getSchemaXml());
			break;
		case 'mysql':
			$target_db = 'mysql';
			$schema_xml = Dase_DB::getSchemaXml();
			$sx = simplexml_load_string($schema_xml);
			$out = '';

			$types['mysql']['bigint'] = 'int';
			$types['mysql']['boolean'] = 'tinyint';
			$types['mysql']['character varying'] = "varchar";
			$types['mysql']['double precision'] = "REAL";
			$types['mysql']['double'] = "REAL";
			$types['mysql']['integer'] = 'int';
			$types['mysql']['int'] = 'int';
			$types['mysql']['mediumtext'] = "text";
			$types['mysql']['text'] = "text";

			foreach ($sx->table as $table) {
				if ($request->has('prefix')) {
					//todo: figure out implementing table prefixes in config as well
					//$table['name'] = $request->get('prefix').'_'.$table['name'];
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
			$request->response_mime_type = 'text/plain';
			$request->renderResponse($out);
			break;
		default:
			$request->renderResponse(Dase_DB::getSchemaXml());
		}
	}

	public function getManagerEmail($request) 
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
		$request->response_mime_type = 'text/plain';
		$request->renderResponse(join("\n",array_unique($managers)));
	}

	public function getUtPerson($request) 
	{
		if ($request->has('lastname')) {
			$person = Utlookup::lookup($request->get('lastname'),'sn');
		} else {
			$person = Utlookup::getRecord($request->get('eid'));
		}
		$request->response_mime_type = 'text/plain';
		$request->renderResponse(var_export($person,true));
	}

	public function getDocs($request)
	{
		// note: doc comments are only displayed
		// on first web view after a file is updated,
		// indicating that a bytecode cache is removing comments

		$tpl = new Dase_Template($request);
		$dir = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(DASE_PATH.'/lib'));
		foreach ($dir as $file) {
			$matches = array();
			if ( 
				false === strpos($file->getPathname(),'smarty') &&
				false === strpos($file->getPathname(),'Smarty') &&
				false === strpos($file->getPathname(),'getid3') &&
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
		if ($request->has('class_id')) {
			$tpl->assign('phpversion',phpversion()); 
			$tpl->assign('class_id',$request->get('class_id')); 
			$documenter = new Documenter($class_list[$request->get('class_id')]);
			$tpl->assign('default_properties',$documenter->getDefaultProperties());
			$tpl->assign('doc',$documenter);
		}
		$request->renderResponse($tpl->fetch('manage/docs.tpl'));
	}

	public function getCollectionForm($request)
	{
		$tpl = new Dase_Template($request);
		$request->renderResponse($tpl->fetch('manage/collection_form.tpl'));
	}

	public function getMediaAttributes($request)
	{
		$media_atts = new Dase_DBO_MediaAttribute;
		$media_atts->orderBy('label');
		$t = new Dase_Template($request);
		$t->assign('attributes',$media_atts->find());  
		$request->renderResponse($t->fetch('manage/media_attributes.tpl'));
	}

	public function putMediaAttribute($request)
	{
		$media_att = new Dase_DBO_MediaAttribute;
		$media_att->load($params['id']);
		$media_att->term = $request->get('term');
		$media_att->label = $request->get('label');
		$media_att->update();
		$params['msg'] = "updated media attribute";
		$request->renderRedirect('manage/media_attributes',$params);
	}
}

