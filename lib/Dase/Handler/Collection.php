<?php

class Dase_Handler_Collection extends Dase_Handler
{
	public $collection;
	public $resource_map = array(
		'{collection_ascii_id}/ping' => 'ping',
		'{collection_ascii_id}' => 'collection',
		'{collection_ascii_id}/archive' => 'archive',
		'{collection_ascii_id}/attributes' => 'attributes',
		'{collection_ascii_id}/service' => 'service',
		'{collection_ascii_id}/items/recent' => 'recent_items',
		'{collection_ascii_id}/attributes/tallies' => 'attribute_tallies',
		'{collection_ascii_id}/attributes/{filter}' => 'attributes',
		'{collection_ascii_id}/attributes/{filter}/tallies' => 'attribute_tallies',
	);

	protected function setup($request)
	{
		$this->collection = Dase_DBO_Collection::get($request->get('collection_ascii_id'));
		if (!$this->collection) {
			$request->renderError(404);
		}
		if ('html' == $request->format && 
			'service' != $request->resource &&
			'ping' != $request->resource 
		) {
			$this->user = $request->getUser();
			if (!$this->user->can('read','collection',$this->collection)) {
				$request->renderError(401);
			}
		}
		/*
		if ('atom' == $request->format) {
			$this->user = $request->getUser('http');
			if (!$this->user->can('read','collection',$this->collection)) {
			$request->renderError(401);
			}
		}
		 */
	}

	public function getPing($request)
	{
		$request->renderResponse('ok');
	}

	public function getRecent($request)
	{
		//this is trickir than it seems (lovely RFC 3339)
	}

	public function getCollectionAtom($request) 
	{
		if ($request->has('limit')) {
		   $limit = $request->get('limit');
		} else {
			$limit = 10;
		}
		$request->renderResponse($this->collection->asAtom($limit));
	}

	public function getArchive($request) 
	{
		$archive = CACHE_DIR.$this->collection->ascii_id.'_'.time();
		file_put_contents($archive,$this->collection->asAtomArchive());
		$request->serveFile($archive,'text/plain',true);
	}

	public function getArchiveJson($request) 
	{
		$archive = CACHE_DIR.$this->collection->ascii_id.'_'.time().'.json';
		file_put_contents($archive,$this->collection->asJsonArchive());
		$request->serveFile($archive,'text/plain',true);
	}

	public function getArchiveAtom($request) 
	{
		$limit = $request->get('limit');
		$request->renderResponse($this->collection->asAtomArchive($limit));
	}

	public function asAtomFull($request) 
	{
		$c = Dase_Collection::get($request->get('collection_ascii_id'));
		$request->renderResponse($c->asAtomFull());
	}

	public function deleteCollection($request)
	{
		$user = $request->getUser('http');
		if (!$user->isSuperuser()) {
			$request->renderError(401,$user->eid.' is not permitted to delete a collection');
		}
		if ($this->collection->getItemCount() < 5) {
			$archive_dir = Dase_Config::get('path_to_media').'/'.$this->collection->ascii_id.'/archive';
			if (!file_exists($archive_dir)) {
				mkdir($archive_dir);
				Dase_Log::info('created directory '.$archive_dir);
				chmod($archive_dir,0775);
			}
			$archive = Dase_Config::get('path_to_media').'/'.$this->collection->ascii_id.'/archive/'.$this->collection->ascii_id.'.atom';
			file_put_contents($archive,$this->collection->asAtomArchive());
			$this->collection->expunge();
			$request->renderResponse('delete succeeded',false,200);
		} else {
			$request->renderError(403,'cannot delete collection with more than 5 items');
		}
	}

	public function getCollection($request) 
	{
		$tpl = new Dase_Template($request);
		$tpl->assign('collection',Dase_Atom_Feed::retrieve(APP_ROOT.'/collection/'.$request->get('collection_ascii_id').'.atom'));
		$request->renderResponse($tpl->fetch('collection/browse.tpl'));
	}

	public function getServiceAtom($request) 
	{
		$request->renderResponse($this->collection->getAtompubServiceDoc(),'application/atomsvc+xml');
	}


	public function postToCollection($request) 
	{
		$this->user = $request->getUser('http');
		if (!$this->user->can('write','collection',$this->collection)) {
			$request->renderError(401,'no go unauthorized');
		}
		$content_type = $request->getContentType();

		if ('application/atom+xml;type=entry' == $content_type ||
		'application/atom+xml' == $content_type ) {
			$this->_newAtomItem($request);
		} elseif ('application/json' == $content_type) {
			$this->_newJsonItem($request);
		} else {
			$request->renderError(415,'cannot accept '.$content_type);
		}
	}

	private function _newAtomItem($request)
	{
		$raw_input = file_get_contents("php://input");
		$client_md5 = $request->getHeader('Content-MD5');
		//if Content-MD5 header isn't set, we just won't check
		if ($client_md5 && md5($raw_input) != $client_md5) {
			$request->renderError(412,'md5 does not match');
		}
		$item_entry = Dase_Atom_Entry::load($raw_input);
		if ('item' != $item_entry->entrytype) {
			$item_entry->setEntryType('item');
			$request->renderError(400,'must be an item entry');
		}
		$item = $item_entry->insert($request);
		header("HTTP/1.1 201 Created");
		header("Content-Type: application/atom+xml;type=entry;charset='utf-8'");
		header("Location: ".APP_ROOT."/item/".$request->get('collection_ascii_id')."/".$item->serial_number.'.atom');
		echo $item->asAtom();
		exit;
	}

	private function _newJsonItem($request)
	{
		$request->renderResponse('still working on JSON posts!');
	}

	public function rebuildIndexes($request) 
	{
		$c = Dase_Collection::get($request->get('collection_ascii_id'));
		$c->buildSearchIndex();
		$params['msg'] = "rebuilt indexes for $c->collection_name";
		$request->renderRedirect('',$params);
	}

	public function getAttributesAtom($request) 
	{
		$request->renderResponse($this->collection->getAttributesAtom()->asXml());
	}

	public function getAttributesJson($request) 
	{
		$filter = $request->has('filter') ? $request->get('filter') : '';
		$request->checkCache();
		$c = $this->collection;
		$attributes = new Dase_DBO_Attribute;
		$attributes->collection_id = $c->id;
		if ('public' == $filter) {
			$attributes->is_public = true;
		}
		if ('admin' == $filter) {
			$attributes->collection_id = 0;
		}
		$attributes->orderBy('sort_order');
		//$attributes->orderBy('attribute_name');
		$att_array = array();
		foreach($attributes->find() as $att) {
			$att_array[] =
				array(
					'id' => $att->id,
					'ascii_id' => $att->ascii_id,
					'attribute_name' => $att->attribute_name,
					'input_type' => $att->html_input_type,
					'collection' => $request->get('collection_ascii_id')
				);
		}
		$request->renderResponse(Dase_Json::get($att_array),$request);
	}

	public function getAttributeTalliesJson($request) 
	{
		$request->checkCache(1500);
		if ($request->has('filter') && ('admin' == $request->get('filter'))) {
			$request->renderResponse(Dase_Json::get($this->_adminAttributeTalliesJson()));
			exit;
		}
		$c = $this->collection;
		$sql = "
			SELECT id, ascii_id
			FROM attribute
			WHERE attribute.collection_id = ?
			AND attribute.is_public = true;
		";
		$st = Dase_DBO::query($sql,array($c->id));
		$sql = "SELECT count(DISTINCT value_text) FROM value WHERE attribute_id = ?";
		$db = Dase_DB::get();
		$sth = $db->prepare($sql);
		$tallies = array();
		while ($row = $st->fetch()) {
			$sth->execute(array($row['id']));
			$tallies[$row['ascii_id']] = $sth->fetchColumn();
		}
		$result['tallies'] = $tallies;
		$result['is_admin'] = 0;
		$request->renderResponse(Dase_Json::get($result));
	}

	private function _adminAttributeTalliesJson() 
	{
		$c = $this->collection;
		$sql = "
			SELECT id, ascii_id
			FROM attribute
			WHERE attribute.collection_id = 0
			";
		$st = Dase_DBO::query($sql);
		$sql = "
			SELECT count(DISTINCT value_text) 
			FROM value WHERE attribute_id = ? 
			AND value.item_id IN
			(SELECT id FROM item
			WHERE item.collection_id = $c->id)
			";
		$db = Dase_DB::get();
		$sth = $db->prepare($sql);
		$tallies = array();
		while ($row = $st->fetch()) {
			$sth->execute(array($row['id']));
			$tallies[$row['ascii_id']] = $sth->fetchColumn();
		}
		$result['tallies'] = $tallies;
		$result['is_admin'] = 1;
		return $result;
	}

	public function itemsByTypeAsAtom($request) {
		$item_type = new Dase_DBO_ItemType;
		$item_type->ascii_id = $request->get('item_type_ascii_id');
		$item_type->findOne();
		$request->renderResponse($item_type->getItemsAsFeed());
	}

	public function buildIndex($request) 
	{
		$c = Dase_Collection::get($request->get('collection_ascii_id'));
		$c->buildSearchIndex();
		$params['msg'] = "rebuilt indexes for $c->collection_name";
		$request->renderRedirect('',$params);
	}

	public function getServiceTxt($request)
	{
		$this->getService($request);
	}

	public function getService($request)
	{
		$request->response_mime_type = 'application/atomsvc+xml';
		$request->renderResponse($this->collection->getAtompubServiceDoc());
	}

}

