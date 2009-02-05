<?php

class Dase_ModuleHandler_Itsprop extends Dase_Handler {

	public $app_root = APP_ROOT;
	public $resource_map = array(
		'test' => 'test',
		'/' => 'welcome',
		'index' => 'home',
		'home' => 'home',
		'home_form' => 'home_form',
		'welcome' => 'welcome',	
		'login' => 'login',
		'logout' => 'logout',
		'person/{eid}' => 'person',
		'person/{eid}/proposal_form' => 'proposal_form',
		'proposals' => 'proposals',
		'proposal/{serial_number}' => 'proposal',
		'proposal/{serial_number}/archiver' => 'proposal_archiver',
		'proposal/{serial_number}/preview' => 'proposal_preview',
		'proposal/{serial_number}/courses' => 'proposal_courses',
		'proposal/{serial_number}/budget_items' => 'proposal_budget_items',
		'persons' => 'persons',
		'departments' => 'departments',
		'department/{dept_id}' => 'department',
		'department/{dept_id}/proposals' => 'department_proposals',
		'service_pass/{serviceuser}' => 'service_pass',
	);

	public function setup($r)
	{
		if ('welcome' != $r->resource && 'login' != $r->resource) {
			$this->user = $r->getUser('cookie',false);
			if (!$this->user) {
				$r->renderRedirect(APP_ROOT.'/modules/'.$r->module.'/welcome');
			} else {
				$eid=$this->user->eid;
				$r->set('chair_feed',Dase_Atom_Feed::retrieve(APP_ROOT. "/search.atom?itsprop.dept_chair_eid=$eid"));

				$is_super = $this->_isSuperuser($this->user->eid);
				if ($is_super) {
					$this->is_superuser = true;
					$r->set('is_superuser',1);
				} else {
					$this->is_superuser = false;
				}
				$this->service_pass = Dase_Auth::getServicePassword('itsprop');
			}
		}
	}

	private function _isSuperuser($eid) 
	{
		$mans = Dase_Json::toPhp(file_get_contents($this->app_root.'/collection/itsprop/managers.json'));
		if (count($mans) && isset($mans[$eid]) && 'superuser' == $mans[$eid]) {
			return true;
		} else {
			return false;
		}
	}

	public function getDepartment($r)
	{
		$tpl = new Dase_Template($r,true);
		$tpl->assign('user',$this->user);
		if ($r->get('eid') != $this->user->eid) {
			if (!$r->is_superuser) {
				$r->renderError(401);
			}
		}
		$dept = Dase_Atom_Entry::retrieve(APP_ROOT. "/item/itsprop/dept-".$r->get('dept_id').".atom");
		if (is_numeric($dept)) {
			$r->renderError($dept);
		}
		$tpl->assign('dept',$dept);
		$tpl->assign('cola_dept',Dept::getDept($r->get('dept_id')));
		$r->renderResponse($tpl->fetch('dept.tpl'));
	}

	public function getDepartmentProposals($r)
	{
		$tpl = new Dase_Template($r,true);
		$tpl->assign('user',$this->user);
		$dept = Dase_Atom_Entry::retrieve(APP_ROOT. "/item/itsprop/dept-".$r->get('dept_id').".atom");
		if (is_numeric($dept)) {
			$r->renderError($dept);
		}
		$tpl->assign('props_link', $dept->getChildfeedLinkUrlByTypeJson('proposal'));
		$tpl->assign('dept',$dept);
		$r->renderResponse($tpl->fetch('dept_props.tpl'));
	}

	public function postToDepartment($r)
	{
		if (!$r->is_superuser) {
			$r->renderError(401);
		}
		$dept = Dase_Atom_Entry::retrieve(APP_ROOT. "/item/itsprop/dept-".$r->get('id').".atom");
		$metadata_array = $dept->getRawMetadata();

		$request_array = array(
			'dept_name' => $r->get('name'),
			'title' => $r->get('name'),
			'dept_id' => $r->get('id'),
			'dept_chair' => $r->get('chair'),
			'dept_chair_email' => $r->get('chair_email'),
			'dept_chair_eid' => $r->get('chair_eid'),
			'dept_display' => $r->get('display'),
		);

		if ('yes' == $r->get('display')) {
			$dept->setStatus('public');
		}

		if ('no' == $r->get('display')) {
			$dept->setStatus('archive');
		}

		//lookup chair by eid
		if ($dept->getValue('dept_chair_eid') != $r->get('chair_eid')) {
			$ldap = Utlookup::getRecord($r->get('chair_eid'));
			if ($ldap) {
				$request_array['dept_chair'] = $ldap['name'];
				$request_array['dept_chair_email'] = $ldap['email'];
			}
		}

		foreach ($request_array as $ascii => $val) {
			$metadata_array[$ascii] = array($val);
		}

		$dept->replaceMetadata($metadata_array);
		$dept->putToUrl($dept->getEditLink(),'itsprop',$this->service_pass);
		$this->_expireDaseSearchCache();
		$r->renderRedirect(APP_ROOT.'/modules/itsprop/department/'.$r->get('id'));
	}

	private function _expireDaseSearchCache()
	{
		$url = Dase_Config::get('app_root').'/search/recent';
		$ch = curl_init();
		// set URL and other appropriate options
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION,true);
		curl_setopt($ch, CURLOPT_USERPWD,"itsprop:$this->service_pass");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$response = curl_exec($ch);
		$info = curl_getinfo($ch);
		curl_close($ch);
		return $info['http_code'];
	}

	public function getPerson($r) 
	{
		$tpl = new Dase_Template($r,true);
		$tpl->assign('user',$this->user);
		if ($r->get('eid') != $this->user->eid) {
			if (!$r->is_superuser) {
				$r->renderError(401);
			}
		}
		$depts_json = file_get_contents(APP_ROOT.'/item_type/itsprop/department/dept_name/values.json?public_only=1');
		$depts = Dase_Json::toPhp($depts_json);
		$tpl->assign('depts', Dase_Json::toPhp($depts_json));
		$person = Dase_Atom_Entry::retrieve(APP_ROOT. "/item/itsprop/".$r->get('eid').".atom");
		if (is_numeric($person)) {
			$r->renderError($person);
		}
		$tpl->assign('person',$person);
		$r->renderResponse($tpl->fetch('person.tpl'));
	}

	public function getPersons($r) 
	{
		$tpl = new Dase_Template($r,true);
		$tpl->assign('user',$this->user);
		$tpl->assign('person', Dase_Atom_Entry::retrieve(APP_ROOT. "/item/itsprop/".$this->user->eid.".atom"));
		$tpl->assign('persons', Dase_Atom_Feed::retrieve(APP_ROOT. "/item_type/itsprop/person/items.atom"));
		$r->renderResponse($tpl->fetch('persons.tpl'));
	}

	public function postToPersons($r) 
	{
		$ldap = Utlookup::getRecord($r->get('eid'));
		if ($ldap) {
			$person = new Dase_Atom_Entry_Item;
			$person->setTitle($ldap['name']);
			$person->setItemType('person');
			//we set title so auto-titling works in DASe
			$person->addMetadata('title',$ldap['name']); 
			$person->addMetadata('person_name',$ldap['name']); 
			$person->addMetadata('person_eid',$ldap['eid']); 
			$person->addMetadata('person_email',$ldap['email']); 
			$person->addMetadata('person_phone',$ldap['phone']); 
			$person->addMetadata('person_lastname',$ldap['lastname']); 
			$person->setUpdated(date(DATE_ATOM));
			$person->postToUrl(APP_ROOT.'/collection/itsprop','itsprop',$this->service_pass,$ldap['eid']);
			$params['msg'] = 'added user '.$ldap['eid'];
		} else {
			$params['msg'] = 'did not find '.$r->get('eid').' in UT Directory';
		}
		$r->renderRedirect(APP_ROOT.'/modules/'.$r->module.'/persons',$params);
	}

	public function getDepartments($r) 
	{
		$tpl = new Dase_Template($r,true);
		$tpl->assign('user',$this->user);
		$tpl->assign('person', Dase_Atom_Entry::retrieve(APP_ROOT. "/item/itsprop/".$this->user->eid.".atom"));
		$tpl->assign('depts', Dase_Atom_Feed::retrieve(APP_ROOT. "/item_type/itsprop/department/items.atom?sort=dept_name"));
		$r->renderResponse($tpl->fetch('departments.tpl'));
	}

	public function getProposals($r) 
	{
		$tpl = new Dase_Template($r,true);
		$tpl->assign('user',$this->user);
		$tpl->assign('proposals', Dase_Atom_Feed::retrieve(APP_ROOT. "/item_type/itsprop/proposal/items.atom"));
		$r->renderResponse($tpl->fetch('proposals.tpl'));
	}

	public function postToProposalArchiver($r)
	{
		$proposal = Dase_Atom_Entry::retrieve(APP_ROOT. "/item/itsprop/".$r->get('serial_number').".atom");
		$metadata_array = $proposal->getRawMetadata();
		$metadata_array['proposal_submitted'] = array(date(DATE_ATOM));
		$proposal->replaceMetadata($metadata_array);
		$proposal->putToUrl($proposal->getEditLink(),'itsprop',$this->service_pass);
		$params['msg'] = "your proposal has been submitted";
		$r->renderRedirect(APP_ROOT.'/modules/itsprop/home',$params);
	}

	public function postToPerson($r)
	{
		if ($r->get('eid') != $this->user->eid) {
			if (!$r->is_superuser) {
				$r->renderError(401);
			}
		}
		$person = Dase_Atom_Entry::retrieve(APP_ROOT. "/item/itsprop/".$r->get('eid').".atom");
		$metadata_array = $person->getRawMetadata();
		$dept_array = $person->getParentLinkNodesByItemType('department');
		if (count($dept_array)) {
			$dept = $dept_array[0];
			$dept->removeAttribute('href');
			$dept->setAttribute('href',$r->get('department'));
		} else {
			$person->addLink($r->get('department'),'http://daseproject.org/relation/parent');
		}
		if ($r->get('refresh')) {
			$ldap = Utlookup::getRecord($r->get('eid'));
			$request_array['title'] = $ldap['name']; 
			$request_array['person_name'] = $ldap['name']; 
			$request_array['person_eid'] = $ldap['eid']; 
			$request_array['person_email'] = $ldap['email']; 
			$request_array['person_phone'] = $ldap['phone']; 
			$request_array['person_lastname'] = $ldap['lastname']; 
		} else {
			$request_array = array(
				'person_name' => $r->get('name'),
				'person_eid' => $r->get('eid'),
				'person_email' => $r->get('email'),
				'person_phone' => $r->get('phone'),
			);
		}
		foreach ($request_array as $ascii => $val) {
			$metadata_array[$ascii] = array($val);
		}

		$person->replaceMetadata($metadata_array);
		$person->putToUrl($person->getEditLink(),'itsprop',$this->service_pass);
		$r->renderRedirect(APP_ROOT.'/modules/itsprop/person/'.$r->get('eid'));
	}

	public function getHome($r) 
	{
		$tpl = new Dase_Template($r,true);
		$tpl->assign('user',$this->user);
		$home = Dase_Atom_Entry::retrieve(APP_ROOT. "/item/itsprop/page-home.atom");
		$tpl->assign('home',$home);
		$r->renderResponse($tpl->fetch('home.tpl'));
	}

	public function getHomeForm($r) 
	{
		if (!$this->is_superuser) {
			$r->renderError(404);
		}
		$tpl = new Dase_Template($r,true);
		$tpl->assign('user',$this->user);
		$home = Dase_Atom_Entry::retrieve(APP_ROOT. "/item/tisprop/page-home.atom");
		if (!$home) {
			$page = new Dase_Atom_Entry_Item;
			$page->setTitle('homepage');
			$page->setItemType('page');
			$page->addMetadata('title','homepage'); 
			$page->addMetadata('page_uri','/home'); 
			$page->setUpdated(date(DATE_ATOM));
			$page->postToUrl(APP_ROOT.'/collection/itsprop','itsprop',$this->service_pass,'page-home');
		}
		$home = Dase_Atom_Entry::retrieve(APP_ROOT. "/item/itsprop/page-home.atom");
		$tpl->assign('home',$home);
		$r->renderResponse($tpl->fetch('home_form.tpl'));
	}

	public function postToHomeForm($r) 
	{
		if (!$this->is_superuser) {
			$r->renderError(404);
		}
		if ('cancel' != $r->get('cancel')) {
			$home = Dase_Atom_Entry::retrieve(APP_ROOT. "/item/itsprop/page-home.atom");
			$home->replaceContent($r->get('home_text'));
			$home->putToUrl($home->getEditLink(),'itsprop',$this->service_pass);
		}
		$r->renderRedirect(APP_ROOT.'/modules/itsprop/home');
	}

	public function getWelcome($r) 
	{
		Uteid::logout($r);
		//$user = $r->getUser();
		$tpl = new Dase_Template($r,true);
		$r->renderResponse($tpl->fetch('welcome.tpl'));
	}

	public function getProposalForm($r)
	{
		$tpl = new Dase_Template($r,true);
		$tpl->assign('user',$this->user);
		$depts_json = file_get_contents(APP_ROOT.'/item_type/itsprop/department/dept_name/values.json?public_only=1');
		$tpl->assign('depts', Dase_Json::toPhp($depts_json));
		$person = Dase_Atom_Entry::retrieve(APP_ROOT. "/item/itsprop/".$this->user->eid.".atom");
		$tpl->assign('person',$person);
		$r->renderResponse($tpl->fetch('proposal_form.tpl'));
	}

	public function getProposal($r)
	{
		$tpl = new Dase_Template($r,true);
		$tpl->assign('user',$this->user);
		$depts_json = file_get_contents(APP_ROOT.'/item_type/itsprop/department/dept_name/values.json?public_only=1');
		$tpl->assign('depts', Dase_Json::toPhp($depts_json));
		$proposal = Dase_Atom_Entry::retrieve(APP_ROOT. "/item/itsprop/".$r->get('serial_number').".atom");
		if ($proposal->getValue('proposal_submitted')) {
			$r->renderRedirect(APP_ROOT.'/modules/itsprop/proposal/'.$r->get('serial_number').'/preview');
		}

		//$person = Dase_Atom_Entry::retrieve(APP_ROOT. "/item/itsprop/".$this->user->eid.".atom");
		$person = Dase_Atom_Entry::retrieve(APP_ROOT. "/item/itsprop/".$proposal->getAuthorName().".atom");
		$tpl->assign('person',$person);

		if (is_numeric($proposal)) {
			$r->renderResponse($tpl->fetch('proposal404.tpl'));
		}
		$tpl->assign('courses',$proposal->getChildfeedLinkUrlByTypeJson('course'));
		$tpl->assign('budget_items',$proposal->getChildfeedLinkUrlByTypeJson('budget_item'));
		$tpl->assign('proposal',$proposal);
		$tpl->assign('previewLink',APP_ROOT.'/modules/itsprop/proposal/'.$r->get('serial_number').'/preview');
		$r->renderResponse($tpl->fetch('proposal.tpl'));
	}

	public function getProposalPreview($r)
	{
		$tpl = new Dase_Template($r,true);
		$tpl->assign('user',$this->user);
		$depts_json = file_get_contents(APP_ROOT.'/item_type/itsprop/department/dept_name/values.json');
		$tpl->assign('depts', Dase_Json::toPhp($depts_json));
		$proposal = Dase_Atom_Entry::retrieve(APP_ROOT. "/item/itsprop/".$r->get('serial_number').".atom");
		if (is_numeric($proposal)) {
			$r->renderResponse($tpl->fetch('proposal404.tpl'));
		}
		$person = Dase_Atom_Entry::retrieve(APP_ROOT. "/item/itsprop/".$proposal->getAuthorName().".atom");
		$tpl->assign('person',$person);
		$tpl->assign('courses',Dase_Json::toPhp(file_get_contents($proposal->getChildfeedLinkUrlByTypeJson('course'))));
		$budget_items = Dase_Json::toPhp(file_get_contents($proposal->getChildfeedLinkUrlByTypeJson('budget_item')));
		$grand_total = 0;
		$display_bud = array();
		foreach ($budget_items as $bud) {
			$p = $bud['metadata']['budget_item_price'];
			$q = $bud['metadata']['budget_item_quantity'];
			$bud['total'] = $p*$q;
			$grand_total += $bud['total'];
			$display_bud[] = $bud;

		}
		$tpl->assign('grand_total',$grand_total);
		$tpl->assign('budget_items',$display_bud);
		$tpl->assign('proposal',$proposal);
		$tpl->assign('propLink',APP_ROOT.'/modules/itsprop/proposal/'.$r->get('serial_number'));
		$r->renderResponse($tpl->fetch('preview.tpl'));
	}

	public function postToProposalForm($r)
	{
		$proposal = new Dase_Atom_Entry_Item;
		$proposal->setTitle($r->get('proposal_name'));
		$proposal->setItemType('proposal');
		$proposal->addAuthor($this->user->eid);
		$proposal->addMetadata('title',$r->get('proposal_name')); 
		$proposal->addMetadata('proposal_budget_description','enter budget description here'); 
		$proposal->addMetadata('proposal_collaborators','enter collaborators here'); 
		$proposal->addMetadata('proposal_description','enter description here'); 
		$proposal->addMetadata('proposal_name',$r->get('proposal_name')); 
		$proposal->addMetadata('proposal_previous_funding','enter previous funding here'); 
		$proposal->addMetadata('proposal_professional_assistance','enter professional assistance here'); 
		$proposal->addMetadata('proposal_faculty_workshop','no'); 
		$proposal->addMetadata('proposal_sta','no'); 
		$proposal->addMetadata('proposal_project_type',$r->get('proposal_project_type')); 
		$proposal->addMetadata('proposal_renovation_description','enter renovation description here'); 
		$proposal->addMetadata('proposal_summary','enter summary here'); 

		$proposal->setUpdated(date(DATE_ATOM));
		//department is a url
		$proposal->addLink($r->get('department'),'http://daseproject.org/relation/parent');
		//person too
		$user_url =  $this->app_root.'/item/itsprop/'.$this->user->eid;
		$proposal->addLink($user_url,'http://daseproject.org/relation/parent');
		$result = $proposal->postToUrl(APP_ROOT.'/collection/itsprop','itsprop',$this->service_pass);
		if (Dase_Util::isUrl($result)) {
			$parts = explode('/',trim($result));
			$sernum = str_replace('.atom','',array_pop($parts));
			$r->renderRedirect(APP_ROOT.'/modules/itsprop/proposal/'.$sernum);
		} else {
			$r->renderError(400,$result);
		}
	}

	/** this will be called ajaxily */
	public function postToProposalCourses($r)
	{
		$course = new Dase_Atom_Entry_Item;
		$course->setTitle($r->get('course_title'));
		$course->setItemType('course');
		$course->addAuthor($this->user->eid);
		$course->addMetadata('title',$r->get('course_title')); 
		$course->addMetadata('course_title',$r->get('course_title')); 
		$course->addMetadata('course_number',$r->get('course_number')); 
		$course->addMetadata('course_frequency',$r->get('course_frequency')); 
		$course->addMetadata('course_enrollment',$r->get('course_enrollment')); 
		$course->setUpdated(date(DATE_ATOM));
		$course->addLink($r->get('proposal'),'http://daseproject.org/relation/parent');
		$result = $course->postToUrl(APP_ROOT.'/collection/itsprop','itsprop',$this->service_pass);
		if (Dase_Util::isUrl($result)) {
			$r->renderResponse($result);
		} else {
			$r->renderError(400,$result);
		}
	}

	public function postToProposalBudgetItems($r)
	{
		$budget_item = new Dase_Atom_Entry_Item;
		$budget_item->setTitle($r->get('budget_item_title'));
		$budget_item->setItemType('budget_item');
		$budget_item->addAuthor($this->user->eid);
		$budget_item->addMetadata('title',$r->get('budget_item_description')); 
		$budget_item->addMetadata('budget_item_description',$r->get('budget_item_description')); 
		$budget_item->addMetadata('budget_item_price',$r->get('budget_item_price')); 
		$budget_item->addMetadata('budget_item_quantity',$r->get('budget_item_quantity')); 
		$budget_item->addMetadata('budget_item_type',$r->get('budget_item_type')); 
		$budget_item->setUpdated(date(DATE_ATOM));
		$budget_item->addLink($r->get('proposal'),'http://daseproject.org/relation/parent');
		$result = $budget_item->postToUrl(APP_ROOT.'/collection/itsprop','itsprop',$this->service_pass);
		if (Dase_Util::isUrl($result)) {
			$r->renderResponse($result);
		} else {
			$r->renderError(400,$result);
		}
	}

	public function getServicePass($r)
	{
		$secret = Dase_Cookie::get('module');
		$suser = $r->get('serviceuser');
		//checks the secret that was saved in cookie upon login
		if ($secret == Dase_Auth::getSecret($r->get('serviceuser'))) {
			$r->renderResponse(Dase_Auth::getServicePassword($suser));
		} else {
			$r->renderError(401);
		}
	}

	public function getLogin($r)
	{
		$user = Uteid::login($r);
		$secret = Dase_Auth::getSecret('itsprop');
		//this secret will be saved as a cookie on the client
		//ONLY upon successful eid login.  Now the client can
		//request the service password (the server will check for 
		//this token before it returns the service password).
		Dase_Cookie::set('module',$secret);
		$ldap = Utlookup::getRecord($user->eid);

		$service_pass = Dase_Auth::getServicePassword('itsprop');

		$person = new Dase_Atom_Entry_Item;
		$person->setTitle($ldap['name']);
		$person->setItemType('person');
		//we set title so auto-titling works in DASe
		$person->addMetadata('title',$ldap['name']); 
		$person->addMetadata('person_name',$ldap['name']); 
		$person->addMetadata('person_eid',$ldap['eid']); 
		$person->addMetadata('person_email',$ldap['email']); 
		$person->addMetadata('person_phone',$ldap['phone']); 
		$person->addMetadata('person_lastname',$ldap['lastname']); 
		$person->setUpdated(date(DATE_ATOM));
		//will automatically fail if user exists (409, I think)
		$person->postToUrl(APP_ROOT.'/collection/itsprop','itsprop',$service_pass,$user->eid);
		$r->renderRedirect(APP_ROOT.'/modules/'.$r->module.'/home');
	}

	public function getLogout($r)
	{
		Uteid::logout($r);
		Dase_Cookie::clear();
		Dase_Cookie::clearByType('module');
		$r->renderRedirect(APP_ROOT.'/modules/'.$r->module.'/welcome');
	}

}
