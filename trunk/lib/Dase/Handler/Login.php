<?php

class Dase_Handler_Login extends Dase_Handler
{
	public $resource_map = array(
		'/' => 'login',
		'form' => 'login_form',
		'{eid}' => 'login',
	);

	protected function setup($r)
	{
		$this->db = $r->retrieve('db');
	}

	public function getLogin($r)
	{
		$t = new Dase_Template($r);
		//'target' is the page to redirect to after login is complete
		$t->assign('target',$r->get('target'));
		$r->renderResponse($t->fetch('login_form.tpl'));
	}

	public function getLoginForm($r)
	{
		//target is empty in the most common case
		//but it *is* used to get people back to
		//their place if they are prompted in the middle of things
		$t = new Dase_Template($r);
		$t->assign('target',$r->get('target'));
		$r->renderResponse($t->fetch('login_form.tpl'));
	}

	public function postToLogin($r)
	{
		//this is the default, uber-simple login
		//which should be overidden by a module
		//all valid users need to be superusers
		$username = strtolower($r->get('username'));
		$pass = $r->get('password');
		$superusers = $r->dase_http_auth->getSuperusers();
		if (isset($superusers[$username]) && $superusers[$username] == $pass) {
			$r->retrieve('cookie')->setEid($username);
			$dbuser = $r->retrieve('dbuser');
			$dbuser->retrieveByEid($username);
			$r->retrieve('cookie')->set('max',$dbuser->max_items);
			$r->retrieve('cookie')->set('display',$dbuser->display);
			$r->retrieve('dbuser')->initCart();
			//do this so cookie is passed along
			$r->renderRedirect(urldecode($r->get('target')));
		} else {
			//I could probably just display here instead of redirect
			$params['msg'] = 'incorrect username/password';
			$r->renderRedirect("login/form",$params);
		}
	}

	/**
	 * this method will be called
	 * w/ an http delete to '/login' *or* '/login/{eid}'
	 *
	 */
	public function deleteLogin($r)
	{
		Dase_Cookie::clear();
		$r->renderRedirect('login/form');
	}

}

