<?php

class Dase_Handler_Admin extends Dase_Handler
{
	public $resource_map = array(
		'/' => 'collection_form',
		'attributes' => 'attributes',
		'collection/form' => 'collection_form',
		'collections' => 'collections',
		'docs' => 'docs',
		'item_fixer' => 'item_fixer',
		'tools' => 'tools',
		'eid/{eid}' => 'ut_person',
		'log' => 'log',
		'manager/email' => 'manager_email',
		'modules' => 'modules',
		'name/{lastname}' => 'ut_person',
		'palette' => 'palette',
		'phpinfo' => 'phpinfo',
		'user/{eid}' => 'user',
		'users' => 'users',
		'cache' => 'cache',
		'db' => 'db_schema',
	);

	public function setup($r)
	{
		//all routes here require superuser privileges
		$this->user = $r->getUser();
		if ( 'modules' != $r->resource && !$this->user->isSuperuser($r->retrieve('config')->getSuperusers())) {
			$r->renderError(401);
		}
	}

	public function deleteCache($r)
	{
		$num = $r->retrieve('cache')->expunge();
		$r->renderResponse('cache deleted '.$num.' files removed');
	}

	/** web hook */
	public function postToItemFixer($r) 
	{
		$collection = Dase_DBO_Collection::get($this->db,$r->get('collection_ascii_id'));
		if ($collection) {
			$i = 0;
			foreach ($collection->getItems() as $item) {
				$item->flushAtom();
				if (!$item->p_collection_ascii_id) {
					$item->p_collection_ascii_id = $collection->ascii_id;
					if ($item->update()) {
						$i++;
					}
				}
			}
		}
		$r->renderResponse("updated $i items in $collection->collection_name");
	}

	public function getModules($r)
	{
		$tpl = new Dase_Template($r);
		$dir = new DirectoryIterator($r->base_path.'/modules');
		$mods = array();
		foreach ($dir as $file) {
			if ( $file->isDir() && false === strpos($file->getFilename(),'.')) {
				$m = $file->getFilename();
				$name = $m;
				$description = '';
				if (file_exists($file->getPathname().'/inc/meta.php')) {
					//will set name & description
					include($file->getPathname().'/inc/meta.php');
				}
				$mods[$m]['ascii_id'] = $m;
				$mods[$m]['name'] = $name;
				$mods[$m]['description'] = $description;
			}
		}
		ksort($mods);
		$tpl->assign('modules',$mods);
		$r->renderResponse($tpl->fetch('admin/modules.tpl'));
	}

	public function deleteLog($r)
	{
		if ($r->logger()->truncate()) {
			$r->renderResponse('log has been truncated');
		} else {
			$r->renderError(500);
		}
	}

	public function getCollections($r)
	{
		$tpl = new Dase_Template($r);
		$r->renderResponse($tpl->fetch('admin/collections.tpl'));
	}

	public function getPhpinfo($r)
	{
		phpinfo();
		exit;
	}

	public function getUsersJson($r)
	{
		$r->renderResponse(Dase_DBO_DaseUser::listAsJson($this->db));
	}

	public function getUsers($r)
	{
		$tpl = new Dase_Template($r);
		$q = $r->get('q');
		if ($q) {
			$tpl->assign('users',Dase_DBO_DaseUser::findByNameSubstr($this->db,$q));
		}
		$r->renderResponse($tpl->fetch('admin/users.tpl'));
	}

	public function postToUsers($r)
	{
		$u = new Dase_DBO_DaseUser($this->db);
		$u->eid = $r->get('eid');
		if ($u->eid && !$u->findOne()) {
			$u->name = $r->get('name');
			$u->insert();
		}
		$r->renderRedirect('admin/users');
	}

	public function getUser($r)
	{
		$user = Dase_DBO_DaseUser::get($this->db,$r->get('eid'));
		$tpl = new Dase_Template($r);
		$tpl->assign('user',$user);
		$tpl->assign('htpass',$user->getHttpPassword($r->retrieve('config')->getAuth('token')));
		$tpl->assign('tags',$user->getTags(true));
		$tpl->assign('collections',$user->getCollections());
		$r->renderResponse($tpl->fetch('admin/user.tpl'));
	}

	public function getManagerEmail($r) 
	{
		$cms = new Dase_DBO_CollectionManager($this->db);
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
		$dir = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($r->base_path.'/lib'));
		foreach ($dir as $file) {
			$matches = array();
			if ( 
				false === strpos($file->getPathname(),'smarty') &&
				false === strpos($file->getPathname(),'Smarty') &&
				false === strpos($file->getPathname(),'getid3') &&
				false === strpos($file->getPathname(),'htaccess') &&
				false === strpos($file->getPathname(),'svn') &&
				'.' != array_shift(str_split($file->getFilename())) &&
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
		$r->renderResponse($tpl->fetch('admin/docs.tpl'));
	}

	public function getCollectionForm($r)
	{
		$tpl = new Dase_Template($r);
		$r->renderResponse($tpl->fetch('admin/collection_form.tpl'));
	}

	public function getPalette($r)
	{
		$tpl = new Dase_Template($r);
		$r->renderResponse($tpl->fetch('admin/palette.tpl'));
	}

	public function getTools($r)
	{
		$tpl = new Dase_Template($r);
		$colls = new Dase_DBO_Collection($this->db);
		$colls->orderBy('collection_name');
		$tpl->assign('collections',$colls->find());
		$r->renderResponse($tpl->fetch('admin/tools.tpl'));
	}

	public function getAttributes($r)
	{
		$atts = array();
		$tpl = new Dase_Template($r);
		$aa = new Dase_DBO_Attribute($this->db);
		$aa->collection_id = 0;
		$aa->orderBy('attribute_name');
		foreach ($aa->find() as $a) {
			//NOTE that you *must* use clone here!!
			$atts[] = clone $a;
		}
		$tpl->assign('atts',$atts);
		$r->renderResponse($tpl->fetch('admin/attributes.tpl'));
	}

	public function getDbSchemaXml($r)
	{
		$r->renderResponse($this->db->getSchemaXml()); 
	}

	public function getDbSchema($r)
	{
		$sql = $this->db->getSchema(); 
		$tpl = new Dase_Template($r);
		$tpl->assign('sql',$sql);
		$r->renderResponse($tpl->fetch('admin/sql.tpl'));
	}

}
