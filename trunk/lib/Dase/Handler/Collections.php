<?php

class Dase_Handler_Collections extends Dase_Handler
{
	//map uri_templates to resources
	//and create parameters based on templates
	public $resource_map = array(
		'/' => 'collections',
		'item_tallies' => 'item_tallies',
		"pk/{id}/{ddd}" => 'test',
	);

	protected function setup($request)
	{
	}

	public function getTest($request) {
		$user = $request->getUser();
		if ($user->isSuperuser()) {
			$request->checkCache();
			print "hi $user->name";
			exit;
		} else {
			Dase::error(401);
		}
	}

	public function postToCollections($request) 
	{
		$user = $request->getUser('http');
		if (!$user->isSuperuser()) {
			$request->renderError(401,$user->eid.' is not permitted to create a collection');
		}
		$content_type = $request->getContentType();

		if ('application/atom+xml;type=entry' == $content_type) {
			$this->_newAtomCollection($request);
		} elseif ('application/json' == $content_type) {
			$this->_newJsonCollection($request);
		} else {
			$request->renderError(415,'cannot accept '.$content_type);
		}
	}

	private function _newAtomCollection($request)
	{
		$raw_input = file_get_contents("php://input");
		$client_md5 = $request->getHeader('Content-MD5');
		if (md5($raw_input) != $client_md5) {
			$request->renderError(412,'md5 does not match');
		}
		$coll_entry = Dase_Atom_Entry::load($raw_input);
		if ('collection' != $coll_entry->entrytype) {
			$request->renderError(400,'must be a collection entry');
		}
		$ascii_id = $coll_entry->create($request);
		header("HTTP/1.1 201 Created");
		header("Content-Type: application/atom+xml;type=entry;charset='utf-8'");
		header("Location: ".APP_ROOT."/collection/".$ascii_id.'.atom');
		echo Dase_DBO_Collection::get($ascii_id)->asAtomEntry();
		exit;
	}

	private function _newJsonCollection($request)
	{
		$request->renderResponse('still working on JSON posts!');
	}

	public function getCollectionsJson($request) 
	{
		$request->renderResponse(Dase_DBO_Collection::listAsJson());
	}

	public function getCollectionsAtom($request) 
	{
		if ($request->get('get_all')) {
			$public_only = false;
		} else {
			$public_only = true;
		}
		$request->renderResponse(Dase_DBO_Collection::listAsAtom($public_only));
	}

	public function getCollections($request) 
	{
		$tpl = new Dase_Template($request);
		$feed = Dase_Atom_Feed::retrieve(DASE_URL.'/collections?format=atom');
		$tpl->assign('collections',$feed);
		//$tpl->assign('collections',Dase_Atom_Feed::retrieve(DASE_URL.'/atom'));
		$request->renderResponse($tpl->fetch('collection/list.tpl'));
	}

	public function getItemTalliesJson($request) 
	{
		$db = Dase_DB::get();
		$sql = "
			select collection.ascii_id,count(item.id) 
			as count
			from
			collection, item
			where collection.id = item.collection_id
			and item.status_id = 0
			group by collection.id, collection.ascii_id
			";
		$st = $db->query($sql);
		$tallies = array();
		foreach ($st->fetchAll() as $row) {
			$tallies[$row['ascii_id']] = $row['count'];
		}
		$request->renderResponse(Dase_Json::get($tallies),$request);
	}
}

