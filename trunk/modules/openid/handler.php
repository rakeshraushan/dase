<?php
class Dase_ModuleHandler_Openid extends Dase_Handler
{
	public $resource_map = array(
		'/' => 'login',
		'form' => 'login_form',
		'confirmation' => 'confirmation',
		'registration' => 'registration',
		'{eid}' => 'login',
	);

	protected function setup($request)
	{
		$this->save_dir = CACHE_DIR;
		session_save_path($this->save_dir);
		require_once "Auth/OpenID/FileStore.php";
		require_once "Auth/OpenID/Consumer.php";
		require_once "Auth/Yadis/Manager.php";
	}

	//rewrite/replace for alternate authentication
	public function getLogin($request)
	{
		$t = new Dase_Template($request,true);
		//'target' is the page to redirect to after login is complete
		$t->assign('target',$request->get('target'));
		$request->renderResponse($t->fetch('login_form.tpl'));
	}

	public function getRegistration($request)
	{
		$t = new Dase_Template($request,true);
		$request->renderResponse($t->fetch('login_form.tpl'));
	}

	public function getConfirmation($request)
	{
		session_start();
		$store_path = $this->save_dir."/openid_consumer";
		if (!file_exists($store_path) && !mkdir($store_path)) {
			print "Could not create the FileStore directory '$store_path'. ".
				" Please check the effective permissions.";
			exit(0);
		}
		$store = new Auth_OpenID_FileStore($store_path);
		$consumer = new Auth_OpenID_Consumer($store);
		$response = $consumer->complete(APP_ROOT.'/'.$request->getUrl()); 
		if ('success' == $response->status) {
			$eid = trim(str_replace('http://','',$response->getDisplayIdentifier()),'/');
			//life is simpler w/o dots in eid:
			$eid = str_replace('.','_',$eid);
			$eid = str_replace('/','_',$eid);
			Dase_Cookie::set($eid);
			if (!Dase_DBO_DaseUser::init($eid)) {
				$u = new Dase_DBO_DaseUser;
				$u->eid = $eid;
				$u->name = $eid;
				$u->insert();
				if (!Dase_DBO_DaseUser::init($eid)) {
					$request->renderError(500,'cannot initialize user');
				}
				$params = array(
					'msg' => "Welcome, ".$eid,
				);
				$request->renderRedirect('/',$params);
			}
			//do this so cookie is passed along
			$request->renderRedirect(urldecode($request->get('target')));
		} else {
			//I could probably just display here instead of redirect
			$params['msg'] = 'incorrect username/password';
			$request->renderRedirect("login/form",$params);
		}
	}

	public function getLoginForm($request)
	{
		$t = new Dase_Template($request,true);
		$t->assign('target',$request->get('target'));
		$request->renderResponse($t->fetch('login_form.tpl'));
	}

	public function postToLogin($request)
	{
		session_start();
		$store_path = $this->save_dir."/openid_consumer";
		if (!file_exists($store_path) && !mkdir($store_path)) {
			print "Could not create the FileStore directory '$store_path'. ".
				" Please check the effective permissions.";
			exit(0);
		}
		$store = new Auth_OpenID_FileStore($store_path);
		$consumer = new Auth_OpenID_Consumer($store);
		$auth_request = $consumer->begin($request->get('openid_identifier'));
		if ($auth_request) {
			$redirect_url = $auth_request->redirectURL(APP_ROOT,APP_ROOT.'/login/confirmation');
			if (Auth_OpenID::isFailure($redirect_url)) {
				$request->renderError(500,"Could not redirect to server: " . $redirect_url->message);
			} else {
				$request->renderRedirect($redirect_url);
			}
		} else {
			$params['msg'] = 'sorry, try again';
			$request->renderRedirect('login',$params);
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

