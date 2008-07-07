<?php

class Dase_Handler_Login extends Dase_Handler
{
	public $resource_map = array(
		'/' => 'login',
		'form' => 'login_form',
		'{eid}' => 'login',
	);

	protected function setup($request)
	{
	}

	//rewrite/replace for alternate authentication
	public function getLogin($request)
	{
		$t = new Dase_Template($request);
		//'target' is the page to redirect to after login is complete
		$t->assign('target',$request->get('target'));
		$request->renderResponse($t->fetch('login_form.tpl'),$request);
	}

	//used only in *this* login scheme
	public function getLoginForm($request)
	{
		$t = new Dase_Template($request);
		$t->assign('target',$request->get('target'));
		$request->renderResponse($t->fetch('login_form.tpl'),$request);
	}

	//rewrite/replace for alternate authentication
	public function postToLogin($request)
	{
		$username = $request->get('username');
		$pass = $request->get('password');
		if ('tseliot' == $pass) {
			Dase_Cookie::set($username);
			Dase_DBO_DaseUser::init($username);
			//do this so cookie is passed along
			$request->renderRedirect(urldecode($request->get('target')));
		} else {
			//I could probably just display here instead of redirect
			$params['msg'] = 'incorrect username/password';
			$request->renderRedirect("login/form",$params);
		}
	}

	/**
	 * this method will be called
	 * w/ an http delete to '/login' *or* '/login/{eid}'
	 *
	 */
	public function deleteLogin($request)
	{
		Dase_Cookie::clear();
		$request->renderRedirect('login/form');
	}

}

