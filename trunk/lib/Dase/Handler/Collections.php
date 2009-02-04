<?php

class Dase_Handler_Collections extends Dase_Handler
{
	//map uri_templates to resources
	//and create parameters based on templates
	public $resource_map = array(
		'/' => 'collections',
		'data' => 'data',
		'acl' => 'acl',
		"pk/{id}/{ddd}" => 'test',
	);

	protected function setup($r)
	{
	}

	public function getTest($r) {
		$user = $r->getUser();
		if ($user->isSuperuser()) {
			$r->checkCache();
			print "hi $user->name";
			exit;
		} else {
			Dase::error(401);
		}
	}

	public function postToCollections($r) 
	{
		$user = $r->getUser('http');
		if (!$user->isSuperuser()) {
			$r->renderError(401,$user->eid.' is not permitted to create a collection');
		}
		$content_type = $r->getContentType();
		if ('application/atom+xml;type=entry' == $content_type ||
			'application/atom+xml' == $content_type
		) {
			$raw_input = file_get_contents("php://input");
			$client_md5 = $r->getHeader('Content-MD5');
			if ($client_md5 && md5($raw_input) != $client_md5) {
				//todo: fix this
				//$r->renderError(412,'md5 does not match');
			}
			try {
				$coll_entry = Dase_Atom_Entry::load($raw_input);
			} catch(Exception $e) {
				Dase_Log::debug('error',$e->getMessage());
				$r->renderError(400,'bad xml');
			}
			if ('collection' != $coll_entry->entrytype) {
				$r->renderError(400,'must be a collection entry');
			}
			if ( isset( $_SERVER['HTTP_SLUG'] ) ) {
				$r->set('ascii_id',Dase_Util::dirify($_SERVER['HTTP_SLUG']));
			}
			$ascii_id = $coll_entry->create($r);
			header("HTTP/1.1 201 Created");
			header("Content-Type: application/atom+xml;type=entry;charset='utf-8'");
			header("Location: ".APP_ROOT."/collection/".$ascii_id.'.atom');
			echo Dase_DBO_Collection::get($ascii_id)->asAtomEntry();
			exit;
		} else {
			$r->renderError(415,'cannoot accept '.$content_type);
		}
	}

	public function getCollectionsJson($r) 
	{
		$r->renderResponse(Dase_DBO_Collection::listAsJson());
	}

	public function getDataJson($r) 
	{
		$r->renderResponse(Dase_DBO_Collection::dataAsJson());
	}

	public function getAclJson($r) 
	{
		$r->renderResponse(Dase_Json::get(Dase_Acl::generate()));
	}

	public function getCollectionsAtom($r) 
	{
		if ($r->get('get_all')) {
			$public_only = false;
		} else {
			$public_only = true;
		}
		$r->renderResponse(Dase_DBO_Collection::listAsAtom($public_only));
	}

	public function getCollections($r) 
	{
		$r->getUser();
		$tpl = new Dase_Template($r);
		$feed = Dase_Atom_Feed::retrieve(APP_ROOT.'/collections.atom');
		//if no collections, redirect to archive admin screen
		//will force login screen for non-superusers if no collections
		$c = new Dase_DBO_Collection;
		if (!$c->findCount() && $r->getUser()->isSuperuser()) {
			$r->renderRedirect('admin');
		}
		$tpl->assign('collections',$feed);
		$r->renderResponse($tpl->fetch('collection/list.tpl'));
	}
}

