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
			$this->user = $r->getUser('cookie');
		}
	}

	public function getPerson($r) 
	{
		$tpl = new Dase_Template($r,true);
		$tpl->assign('user',$this->user);
		$tpl->assign('person',Dase_Atom_Feed::retrieve(APP_ROOT. "/search.atom?itsprop.person_eid=$this->user->eid"));
		$r->renderResponse($tpl->fetch('person.tpl'));
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
		$r->renderRedirect(APP_ROOT.'/modules/'.$r->module.'/home');
	}

	public function getLogout($r)
	{
		$user = Uteid::logout($r);
		$r->renderRedirect(APP_ROOT.'/modules/'.$r->module.'/welcome');
	}

}
