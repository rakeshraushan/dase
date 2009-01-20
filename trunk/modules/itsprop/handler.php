<?php

class Dase_ModuleHandler_Itsprop extends Dase_Handler {

	public $resource_map = array(
		'/' => 'welcome',
		'index' => 'home',
		'home' => 'home',
		'welcome' => 'welcome',	
		'login' => 'login',
		'logout' => 'logout',
		'person/{eid}' => 'person',
	);

	public function setup($r)
	{
		if ('welcome' != $r->resource && 'login' != $r->resource) {
			$this->user = $r->getUser('cookie',false);
			if (!$this->user) {
				$r->renderRedirect(APP_ROOT.'/modules/'.$r->module.'/welcome');
			}
		}
	}

	public function getPerson($r) 
	{
		$tpl = new Dase_Template($r,true);
		$tpl->assign('user',$this->user);
		//$depts_json = file_get_contents('http://dev.laits.utexas.edu/itsprop/new/item_type/itsprop/department/items.json');
		//$tpl->assign('depts', Dase_Json::toPhp($depts_json));
		$tpl->assign('person', Dase_Atom_Entry::retrieve(APP_ROOT. "/item/itsprop/".$this->user->eid.".atom"));
		$r->renderResponse($tpl->fetch('person.tpl'));
	}

	public function postToPerson($r)
	{
		$person = Dase_Atom_Entry::retrieve(APP_ROOT. "/item/itsprop/".$this->user->eid.".atom");
		list($dept) = $person->getParentLinkNodesByItemType('department');
		$dept->removeAttribute('href');
		$dept->setAttribute('href',$r->get('department'));
		$person->putToUrl($person->getEditLink(),'pkeane','itsprop8');
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
		//$user = $r->getUser();
		$tpl = new Dase_Template($r,true);
		$r->renderResponse($tpl->fetch('welcome.tpl'));
	}

	public function getLogin($r)
	{
		$user = Uteid::login($r);
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
		$person->postToUrl(APP_ROOT.'/collection/itsprop','pkeane','itsprop8',$user->eid);
		$r->renderRedirect(APP_ROOT.'/modules/'.$r->module.'/home');
	}

	public function getLogout($r)
	{
		$user = Uteid::logout($r);
		$r->renderRedirect(APP_ROOT.'/modules/'.$r->module.'/welcome');
	}

}
