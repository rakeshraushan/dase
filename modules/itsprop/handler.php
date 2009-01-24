<?php

class Dase_ModuleHandler_Itsprop extends Dase_Handler {

	public $app_root = APP_ROOT;
	public $resource_map = array(
		'test' => 'test',
		'/' => 'welcome',
		'index' => 'home',
		'home' => 'home',
		'welcome' => 'welcome',	
		'login' => 'login',
		'logout' => 'logout',
		'person/{eid}' => 'person',
		'person/{eid}/proposal_form' => 'proposal_form',
		'proposal/{serial_number}' => 'proposal',
		'persons' => 'persons',
		'departments' => 'departments',
		'service_token' => 'service_token',
	);

	public function setup($r)
	{
		if ('welcome' != $r->resource && 'login' != $r->resource) {
			$this->user = $r->getUser('cookie',false);
			if (!$this->user) {
				$r->renderRedirect(APP_ROOT.'/modules/'.$r->module.'/welcome');
			} else {
				$is_super = $this->_isSuperuser($this->user->eid);
				if ($is_super) {
					$r->set('is_superuser',1);
				}
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

	public function getPerson($r) 
	{
		$tpl = new Dase_Template($r,true);
		$tpl->assign('user',$this->user);
		if ($r->get('eid') != $this->user->eid) {
			if (!$r->is_superuser) {
				$r->renderError(401);
			}
		}
		$depts_json = file_get_contents(APP_ROOT.'/item_type/itsprop/department/dept_name/values.json');
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

	public function getDepartments($r) 
	{
		$tpl = new Dase_Template($r,true);
		$tpl->assign('user',$this->user);
		$tpl->assign('person', Dase_Atom_Entry::retrieve(APP_ROOT. "/item/itsprop/".$this->user->eid.".atom"));
		$tpl->assign('depts', Dase_Atom_Feed::retrieve(APP_ROOT. "/item_type/itsprop/department/items.atom"));
		$r->renderResponse($tpl->fetch('departments.tpl'));
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
		$person->putToUrl($person->getEditLink(),'pkeane','okthen');
		$r->renderRedirect(APP_ROOT.'/modules/itsprop/person/'.$r->get('eid'));
	}

	public function getHome($r) 
	{
		$tpl = new Dase_Template($r,true);
		$tpl->assign('user',$this->user);
		$tpl->assign('home',Dase_Atom_Feed::retrieve(APP_ROOT. "/search.atom?itsprop~title=homepage"));
		$r->renderResponse($tpl->fetch('home.tpl'));
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
		$depts_json = file_get_contents(APP_ROOT.'/item_type/itsprop/department/dept_name/values.json');
		$tpl->assign('depts', Dase_Json::toPhp($depts_json));
		$person = Dase_Atom_Entry::retrieve(APP_ROOT. "/item/itsprop/".$this->user->eid.".atom");
		$tpl->assign('person',$person);
		$r->renderResponse($tpl->fetch('proposal_form.tpl'));
	}

	public function getProposal($r)
	{
		$tpl = new Dase_Template($r,true);
		$tpl->assign('user',$this->user);
		$depts_json = file_get_contents(APP_ROOT.'/item_type/itsprop/department/dept_name/values.json');
		$tpl->assign('depts', Dase_Json::toPhp($depts_json));
		$person = Dase_Atom_Entry::retrieve(APP_ROOT. "/item/itsprop/".$this->user->eid.".atom");
		$tpl->assign('person',$person);
		$proposal = Dase_Atom_Entry::retrieve(APP_ROOT. "/item/itsprop/".$r->get('serial_number').".atom");
		if (is_numeric($proposal)) {
			$r->renderResponse($tpl->fetch('proposal404.tpl'));
		}
		$tpl->assign('proposal',$proposal);
		$r->renderResponse($tpl->fetch('proposal.tpl'));
	}

	public function postToProposalForm($r)
	{
		$proposal = new Dase_Atom_Entry_Item;
		$proposal->setTitle($r->get('proposal_name'));
		$proposal->setItemType('proposal');
		$proposal->addMetadata('title',$r->get('proposal_name')); 
		$proposal->addMetadata('proposal_project_type',$r->get('proposal_project_type')); 
		$proposal->addMetadata('proposal_name',$r->get('proposal_name')); 
		$proposal->setUpdated(date(DATE_ATOM));
		$proposal->addLink($r->get('department'),'http://daseproject.org/relation/parent');
		$result = $proposal->postToUrl(APP_ROOT.'/collection/itsprop','pkeane','okthen');
		$parts = explode('/',trim($result));
		$sernum = str_replace('.atom','',array_pop($parts));
		$r->renderRedirect(APP_ROOT.'/modules/itsprop/proposal/'.$sernum);
	}

	public function getServiceToken($r)
	{
		$secret = Dase_Cookie::get('module');
		if ($secret == md5(Dase_Config::get('token').'itsprop')) {
			//note: 'itsprop' MUST be declared in MODULE_ROOT.'/inc/config.php'
			//as a service user
			$r->renderResponse(md5(Dase_Config::get('service_token').'itsprop'));
		} else {
			$r->renderError(401);
		}
	}

	public function getLogin($r)
	{
		$user = Uteid::login($r);
		$secret = md5(Dase_Config::get('token').'itsprop');
		Dase_Cookie::set('module',$secret);
		$ldap = Utlookup::getRecord($user->eid);
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
		$person->postToUrl(APP_ROOT.'/collection/itsprop','pkeane','okthen',$user->eid);
		$r->renderRedirect(APP_ROOT.'/modules/'.$r->module.'/home');
	}

	public function getLogout($r)
	{
		Uteid::logout($r);
		$r->renderRedirect(APP_ROOT.'/modules/'.$r->module.'/welcome');
	}

}
