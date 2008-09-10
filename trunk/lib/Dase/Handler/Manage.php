<?php

class Dase_Handler_Manage extends Dase_Handler
{
	public $resource_map = array(
		'phpinfo' => 'phpinfo',
		'manager/email' => 'manager_email',
		'eid/{eid}' => 'ut_person',
		'name/{lastname}' => 'ut_person',
		'docs' => 'docs',
		'users' => 'users',
		'user/{eid}' => 'user',
		'collection/form' => 'collection_form',
		'ingest/checker' => 'ingest_checker',
		'ingester' => 'ingester',
		'ingest/form' => 'ingest_form',
		'collections' => 'collections',
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

