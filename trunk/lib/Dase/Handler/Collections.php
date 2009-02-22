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
		$this->db = $r->retrieve('db');
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
			$raw_input = $r->getBody();
			$client_md5 = $r->getHeader('Content-MD5');
			if ($client_md5 && md5($raw_input) != $client_md5) {
				//todo: fix this
				//$r->renderError(412,'md5 does not match');
			}
			try {
				$coll_entry = Dase_Atom_Entry::load($raw_input);
			} catch(Exception $e) {
				$r->logger()->debug('error',$e->getMessage());
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
			header("Location: ".$r->app_root."/collection/".$ascii_id.'.atom');
			echo Dase_DBO_Collection::get($this->db,$ascii_id)->asAtomEntry($r->app_root);
			exit;
		} else {
			$r->renderError(415,'cannoot accept '.$content_type);
		}
	}

	public function getCollectionsJson($r) 
	{
		$r->renderResponse(Dase_DBO_Collection::listAsJson($this->db));
	}

	public function getDataJson($r) 
	{
		$r->renderResponse(Dase_DBO_Collection::dataAsJson($this->db));
	}

	public function getAclJson($r) 
	{
		$r->renderResponse(Dase_Json::get(Dase_Acl::generate($this->db)));
	}

	public function getCollectionsAtom($r) 
	{
		if ($r->get('get_all')) {
			$public_only = false;
		} else {
			$public_only = true;
		}
		$r->renderResponse(Dase_DBO_Collection::listAsAtom($this->db,$r->app_root,$public_only));
	}

	public function getCollections($r) 
	{
		$r->getUser();
		//if no collections, redirect to archive admin screen
		//will force login screen for non-superusers if no collections
		$c = new Dase_DBO_Collection($r->retrieve('db'));
		if (!$c->findCount() && $r->getUser()->isSuperuser()) {
			$r->renderRedirect('admin');
		}
		$feed = Dase_Atom_Feed::retrieve($r->app_root.'/collections.atom');
		$tpl = new Dase_Template($r);
		$tpl->assign('collections',$feed);
		$r->renderResponse($tpl->fetch('collection/list.tpl'));
	}
}

