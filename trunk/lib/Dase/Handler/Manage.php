<?php

class Dase_Handler_Manage extends Dase_Handler
{
	public $resource_map = array(
		'phpinfo' => 'phpinfo',
		'colors' => 'colors',
		'manager/email' => 'manager_email',
		'eid/{eid}' => 'ut_person',
		'name/{lastname}' => 'ut_person',
		'jquery' => 'jquery',
		'docs' => 'docs',
		'docs/{id}' => 'docs',
	);

	public function setup($request)
	{
		//all routes here require superuser privileges
		$user = $request->getUser();
		if (!$user->isSuperuser()) {
			$request->renderError(401);
		}
	}

	public function getPhpinfo($request)
	{
		phpinfo();
		exit;
	}

	public function getColors($request) 
	{
		$tpl = new Dase_Template($request);
		$request->renderResponse($tpl->fetch('palette.tpl'));
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

	public function getJquery($request)
	{
		$tpl = new Dase_Template($request);
		$request->renderResponse($tpl->fetch('manage/jqtest.tpl'));
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
		if ($request->has('id')) {
			$tpl->assign('phpversion',phpversion()); 
			$documenter = new Documenter($class_list[$request->get('id')]);
			$tpl->assign('default_properties',$documenter->getDefaultProperties());
			$tpl->assign('doc',$documenter);
		}
		$request->renderResponse($tpl->fetch('manage/docs.tpl'));
		}
	}

