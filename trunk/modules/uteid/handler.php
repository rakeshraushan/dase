<?php
class Dase_ModuleHandler_Uteid extends Dase_Handler
{
	public $resource_map = array(
		'/' => 'login',
		'{eid}' => 'login',
	);

	protected function setup($r)
	{
	}

	public function getLogin($r)
	{
		$target = $r->get('target');

		if (!extension_loaded("eid")) {
			dl("eid.so");
		}
		if (!extension_loaded("eid")) {
			echo "The eid extension is not loaded into this PHP!<p>\n";
			print_r (get_loaded_extensions());
			exit;
		}
		if (!function_exists("eid_decode"))
		{
			echo "The eid_decode function is not available in this eid extension!<p>\n";
			print_r (get_extension_funcs ("eid"));
			exit;
		}

		$ut_user = eid_decode(); 

		if (isset($ut_user->status) && EID_ERR_OK != $ut_user->status) {
			unset($ut_user);
		}
		if ($ut_user == NULL) {
			$url = $r->app_root . '/login?target='.$target;
			header ("Set-Cookie: DOC=$url; path=/; domain=.utexas.edu;");
			header ("Location: https://utdirect.utexas.edu");
			echo "user is not logged in";
			exit;
		}
		if ($ut_user) {
			switch ($ut_user->status) {
			case EID_ERR_OK:
				//echo "EID decode ok<br>\n";
				break;
			case EID_ERR_INVALID:
				echo "Invalid EID encoding";
				exit;
			case EID_ERR_BADARG:
				echo "Internal error in EID decoding";
				exit;
			case EID_ERR_BADSIG:
				echo "Invalid EID signature";
				exit;
			}

			$db_user = $r->retrieve('user');
			if (!$db_user->retrieveByEid($ut_user->eid)) {
				$db_user->eid = strtolower($ut_user->eid); 
				$db_user->name = $ut_user->name; 
				$db_user->insert();
			}
			$r->setCookie('eid',$db_user->eid);
			$db_user->getHttpPassword($r->retrieve('config')->getAuth('token'));
			if ($target) {
				$r->renderRedirect(urldecode($target));
			} else {
				$r->renderRedirect();
			}
		}
	}

	/**
	 * this method will be called
	 * w/ an http delete to '/login' *or* '/login/{eid}'
	 *
	 */
	public function deleteLogin($r)
	{
		setcookie('DOC','',time()-86400,'/','.utexas.edu');
		setcookie('FC','',time()-86400,'/','.utexas.edu');
		setcookie('SC','',time()-86400,'/','.utexas.edu');
		setcookie('TF','',time()-86400,'/','.utexas.edu');
		$r->retrieve('cookie')->clear();
		//redirect messes up safari!!
		//$r->renderRedirect('login/form');
		$r->renderOk();
	}

}

