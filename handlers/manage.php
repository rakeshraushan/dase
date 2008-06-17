<?php

class ManageHandler extends Dase_Handler
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
		$tpl = new Dase_Template($request);
		$dir = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(DASE_PATH.'/lib'));
		foreach ($dir as $file) {
			$matches = array();
			if ( 
				//omit a couple big 3rd party libs
				false === strpos($file->getPathname(),'smarty') &&
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
		$filter = create_function('$filename', 'return preg_match("/(dase|mimeparse|service|utlookup)/i",$filename);');
		$class_list = array_filter($arr,$filter);
		$tpl->assign('class_list',$class_list);
		$tpl->assign('phpversion',phpversion()); 
		$documenter = new Documenter($class_list[$request->get('id')]);
		$tpl->assign('default_properties',$documenter->getDefaultProperties());
		$tpl->assign('doc',$documenter);
		$request->renderResponse($tpl->fetch('manage/docs.tpl'));

				/*
				$classname = '';
				if ($request->has('class')) {
					$classname = $request->get('class');
					$class = new Documenter($classname);
					echo "<h2>Name: ". $class->getName() . "</h2>\n";
					if(function_exists('date_default_timezone_set')){
						date_default_timezone_set("Canada/Eastern");
					}
					$today = date("M-d-Y");
					echo "<p> Date: $today<br />";
					echo "PHP version: ". phpversion() . "<br />";
					echo "Type: ". $class->getClassType() . "<br /><br />\n";
					echo "<span class=\"fulldescription\">". $class->getFullDescription().
						"</span><br /><br />\n";
					echo "<span class=\"comment\">";
					echo $class->getDocComment() . "</span></p>\n";
					$arr = $class->getPublicMethods();
					if (count ($arr) > 0){
						show_methods($class, "Public Methods", $arr);
					}
					$arr = $class->getProtectedMethods();
					if (count($arr) > 0){
						show_methods($class, "Protected Methods", $arr);
					}
					$arr = $class->getPrivateMethods();
					if (count($arr) > 0){
						show_methods($class, "Private Methods", $arr);
					}
					//now do data members
					$arr = $class->getPublicDataMembers();
					if (count($arr) > 0){
						show_data_members($class, "Public Data Members", $arr);
					}
					$arr = $class->getProtectedDataMembers();
					if (count($arr) > 0){
						show_data_members($class, "Protected Data Members", $arr);
					}
					$arr = $class->getPrivateDataMembers();
					if (count($arr) > 0){
						show_data_members($class, "Private Data Members", $arr);
					}
					$arr = $class->getConstants();
					if (count($arr) > 0){
						echo "<h3>Constants</h3>\n";
						foreach ($arr as $key => $value){
							echo "<p><span class=\"keyword\">const</span> ".
								"<span class=\"name\">$key</span> = $value <br /></p>\n";
						}
					}
			}
				 */
			exit;
		}
	}

