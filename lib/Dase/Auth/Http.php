<?php

class Dase_Auth_Http
{

	public function authorize($params,$type)
	{
		Dase_Auth_Http::basic($params,$type);
		return true;
	}

	public static function basic($params,$type)
	{
		/* how this works:
		 * a UA wanting to make a request using basic http auth
		 * will be needing read, write, or admin auth_level access
		 * to a particular collection as  a particular user. They need
		 * to use an EID auth cookie request to get their password,
		 * which is good until midnight server time. (Susceptible to replay
		 * attack, so should ideally use HTTPS). The request is:
		 * http://<dase_url>/user/<eid>/collection/<collection_ascii_id>/auth/<auth_level>
		 * which will return an 8 character password to be used for Basic Auth
		 * request.  THIS method will check to see if the password is one of the 
		 * 3 possible -- handler re-checks auth level for given route.
		 */

		//make methods for other sorts of requests (like for restricted Atom Feeds)

		if (isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW'])) {
			$eid = $_SERVER['PHP_AUTH_USER'];

			//note that a user would NOT have been able to even *discover* the password
			//for the auth level they seek unless they had that auth level (determined
			//with a cookie-based discovery transaction (with caveats for the limitations
			//of *any* authorization scheme, blah, blah...)
			//
			//the point of this http auth is really just to establish that 
			//this eid (http_user) is authentic. Individual actions still
			//need to be authorized using that eid 

			$passwords = array();
			if ('collection' == $type) {
				foreach (array('read','write','admin') as $level) {
					$passwords[] = Dase_DBO_Collection::getHttpPassword($params['collection_ascii_id'],$eid,$level);
				}
			}
			if ('tag' == $type) {
				foreach (array('read','write','admin') as $level) {
					$passwords[] = Dase_DBO_Tag::getHttpPassword($params['tag_ascii_id'],$eid,$level);
				}
			}
			if (in_array($_SERVER['PHP_AUTH_PW'],$passwords)) {
				Dase_Registry::set('eid',$eid); //since handler needs to re-check auth_level
				//and will *not* be able to verify eid based on cookie
				//Dase::log('standard','authenticated '.$eid.' with password '.$_SERVER['PHP_AUTH_PW']);
				return;
			}
		}
		header('WWW-Authenticate: Basic realm="DASe"');
		header('HTTP/1.1 401 Unauthorized');
		echo "sorry, authorized users only";
		exit;
	}
}

