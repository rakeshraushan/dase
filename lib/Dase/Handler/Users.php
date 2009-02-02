<?php

class Dase_Handler_Users extends Dase_Handler
{
	//map uri_templates to resources
	//and create parameters based on templates
	public $resource_map = array(
		'/' => 'users',
	);

	protected function setup($r)
	{
		$this->user = $r->getUser('http');
		if (!$this->user->isManager()) {
			$r->renderError(401);
		}

	}

	public function postToUsers($r)
	{
		$content_type = $r->getContentType();
		if ('application/atom+xml;type=entry' == $content_type ||
			'application/atom+xml' == $content_type ) {
				$raw_input = file_get_contents("php://input");
				$client_md5 = $r->getHeader('Content-MD5');
				//if Content-MD5 header isn't set, we just won't check
				if ($client_md5 && md5($raw_input) != $client_md5) {
					$r->renderError(412,'md5 does not match');
				}
				$entry = Dase_Atom_Entry::load($raw_input);
				if ('user' != $entry->entrytype) {
					$r->renderError(400,'must be a user entry');
				}
				try {
					$user = $entry->insert($r);
					header("HTTP/1.1 201 Created");
					header("Content-Type: application/atom+xml;type=entry;charset='utf-8'");
					header("Location: ".$user->getBaseUrl().'.atom?type=entry');
					echo $user->asAtomEntry();
					exit;
				} catch (Dase_Exception $e) {
					$r->renderError(409,$e->getMessage());
				}
			} elseif ('application/x-www-form-urlencoded' == $content_type) {
				//in honor of http://www.tbray.org/ongoing/When/200x/2009/01/29/Name-Value-Pairs
				$eid = $r->get('eid');
				$name = $r->get('name');
				$user = Dase_DBO_DaseUser::get($eid);
				if (!$user) {
					$user = new Dase_DBO_DaseUser();
					$user->name = $name; 
					$user->eid = strtolower($eid); 
					$user->updated = date(DATE_ATOM);
					$user->created = date(DATE_ATOM);
					$user->insert();
				}
				header("HTTP/1.1 201 Created");
				header("Content-Type: application/atom+xml;type=entry;charset='utf-8'");
				header("Location: ".$user->getBaseUrl().'.atom?type=entry');
				echo $user->asAtomEntry();
				exit;
			} else {
				$r->renderError(415,'cannot accept '.$content_type);
			}
	}

	public function getUsersAtom($r) 
	{
		$r->renderResponse(Dase_DBO_DaseUser::listAsAtom());
	}
}

