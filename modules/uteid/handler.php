<?php
class Dase_ModuleHandler_Uteid extends Dase_Handler
{
	public $resource_map = array(
		'/' => 'login',
		'{eid}' => 'login',
	);

	protected function setup($request)
	{
	}

	public function getLogin($request)
	{
		$target = $request->get('target');

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
			$url = APP_ROOT . '/login?target='.$target;
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

			$prefix = Dase_Config::get('table_prefix');
			$db = Dase_DB::get();
			$sql = "
				SELECT * FROM {$prefix}dase_user 
				WHERE lower(eid) = ?
				";	
			$sth = $db->prepare($sql);
			$sth->execute(array(strtolower($ut_user->eid)));
			$row = $sth->fetch();
			if ($row) {
				$db_user = new Dase_DBO_DaseUser($row);
			} else {
				$db_user = new Dase_DBO_DaseUser();
				$db_user->name = $ut_user->name; 
				$db_user->eid = strtolower($ut_user->eid); 
				$db_user->insert();
			}
			Dase_Cookie::setEid($db_user->eid);
			Dase_DBO_DaseUser::init($db_user->eid);
			if ($target) {
				$r->renderRedirect(urldecode($target));
			} else {
				$request->renderRedirect();
			}
		}
	}

	/**
	 * this method will be called
	 * w/ an http delete to '/login' *or* '/login/{eid}'
	 *
	 */
	public function deleteLogin($request)
	{
		setcookie('DOC','',time()-86400,'/','.utexas.edu');
		setcookie('FC','',time()-86400,'/','.utexas.edu');
		setcookie('SC','',time()-86400,'/','.utexas.edu');
		setcookie('TF','',time()-86400,'/','.utexas.edu');
		Dase_Cookie::clear();
		//redirect messes up safari!!
		//$request->renderRedirect('login/form');
		$request->renderOk();
	}

}

