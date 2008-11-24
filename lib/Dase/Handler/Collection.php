<?php

class Dase_Handler_Collection extends Dase_Handler
{
	public $collection;
	public $resource_map = array(
		'{collection_ascii_id}/ping' => 'ping',
		'{collection_ascii_id}' => 'collection',
		'{collection_ascii_id}/ingester' => 'ingester',
		'{collection_ascii_id}/serial_numbers' => 'serial_numbers',
		'{collection_ascii_id}/archive' => 'archive',
		'{collection_ascii_id}/attributes' => 'attributes',
		'{collection_ascii_id}/service' => 'service',
		'{collection_ascii_id}/items' => 'items',
		'{collection_ascii_id}/items/recent' => 'recent_items',
		'{collection_ascii_id}/items/by/md5/{md5}' => 'items_by_md5',
		'{collection_ascii_id}/items/by/att/{att_ascii_id}' => 'items_by_att',
		'{collection_ascii_id}/items/that/lack_media' => 'items_that_lack_media',
		'{collection_ascii_id}/items/marked/to_be_deleted' => 'items_marked_to_be_deleted',
		'{collection_ascii_id}/attributes/tallies' => 'attribute_tallies',
		'{collection_ascii_id}/attributes/{filter}' => 'attributes',
		'{collection_ascii_id}/attributes/{filter}/tallies' => 'attribute_tallies',
	);

	protected function setup($r)
	{
		$this->collection = Dase_DBO_Collection::get($r->get('collection_ascii_id'));
		if (!$this->collection) {
			$r->renderError(404);
		}
		if ('html' == $r->format && 
			'service' != $r->resource &&
			'ping' != $r->resource
		) {
			$this->user = $r->getUser();
			if (!$this->user->can('read',$this->collection)) {
				$r->renderError(401);
			}
		}
		/* todo: i guess anyone can read?
		if ('atom' == $r->format) {
			$this->user = $r->getUser('http');
			if (!$this->user->can('read',$this->collection)) {
			$r->renderError(401);
			}
		}
		 */
	}

	public function getSerialNumbersTxt($r)
	{
		$r->checkCache();
		$sernums = $this->collection->getSerialNumbers();
		$r->renderResponse(join('|',$sernums));
	}

	public function getItemsTxt($r) 
	{
		$output = '';
		foreach ($this->collection->getItems() as $item) {
			$output .= $item->serial_number; 
			//pass in 'display' params to view att value
			foreach ($r->get('display',true) as $member) {
				$output .= '|'.$item->getValue($member);
			}
			$output .= "\n";
		}
		$r->renderResponse($output);
	}

	public function getItemsThatLackMediaTxt($r) 
	{
		$output = '';
		$i = 0;
		foreach ($this->collection->getItems() as $item) {
			if (!$item->getMediaCount()) {
				$i++;
				$output .= $item->serial_number; 
				//pass in 'display' params to view att value
				foreach ($r->get('display',true) as $member) {
					$output .= '|'.$item->getValue($member);
				}
				$output .= "\n";
			}
		}
		if ($r->has('get_count')) {
			$output = $i;
		}
		$r->renderResponse($output);
	}

	public function getItemsMarkedToBeDeletedTxt($r) 
	{
		$output = '';
		$items = new Dase_DBO_Item;
		$items->collection_id = $this->collection->id;
		$items->status = 'delete';
		foreach ($items->find() as $item) {
			$output .= $item->serial_number.'|'; 
		}
		$r->renderResponse($output);
	}

	public function getItemsByMd5Txt($r) 
	{
		$file = new Dase_DBO_MediaFile;
		$file->md5 = $r->get('md5');
		$file->p_collection_ascii_id = $this->collection->ascii_id;
		if ($file->findOne()) {
			$r->renderResponse($file->p_serial_number.' is a duplicate');
		} else {
			$r->renderError(404,'no item with checksum '.$r->get('md5'));
		}
	}

	public function getItemsByAttAtom($r)
	{
		$r->renderResponse($this->collection->getItemsByAttAsAtom($r->get('att_ascii_id')));
	}

	public function getPing($r)
	{
		$r->renderResponse('ok');
	}

	public function getRecent($r)
	{
		//this is trickir than it seems (lovely RFC 3339)
	}

	public function getCollectionAtom($r) 
	{
		if ($r->has('limit')) {
		   $limit = $r->get('limit');
		} else {
			$limit = 5;
		}
		$r->renderResponse($this->collection->asAtom($limit));
	}

	public function getArchive($r) 
	{
		$archive = CACHE_DIR.$this->collection->ascii_id.'_'.time();
		file_put_contents($archive,$this->collection->asAtomArchive());
		$r->serveFile($archive,'text/plain',true);
	}

	public function getArchiveJson($r) 
	{
		$archive = CACHE_DIR.$this->collection->ascii_id.'_'.time().'.json';
		file_put_contents($archive,$this->collection->asJsonArchive());
		$r->serveFile($archive,'text/plain',true);
	}

	public function getArchiveAtom($r) 
	{
		$user = $r->getUser('http');
		if (!$user->isSuperuser()) {
			$r->renderError(401,$user->eid.' is not permitted to access the archive of this collection');
		}
		$limit = $r->get('limit');
		$r->renderResponse($this->collection->asAtomArchive($limit));
	}

	public function asAtomFull($r) 
	{
		$c = Dase_Collection::get($r->get('collection_ascii_id'));
		$r->renderResponse($c->asAtomFull());
	}

	public function deleteCollection($r)
	{
		$user = $r->getUser('http');
		if (!$user->isSuperuser()) {
			$r->renderError(401,$user->eid.' is not permitted to delete a collection');
		}
		if ($this->collection->item_count < 5) {
			$archive_dir = Dase_Config::get('path_to_media').'/'.$this->collection->ascii_id.'/archive';
			if (!file_exists($archive_dir)) {
				mkdir($archive_dir);
				Dase_Log::info('created directory '.$archive_dir);
				chmod($archive_dir,0775);
			}
			$archive = Dase_Config::get('path_to_media').'/'.$this->collection->ascii_id.'/archive/'.$this->collection->ascii_id.'.atom';
			file_put_contents($archive,$this->collection->asAtomArchive());
			$this->collection->expunge();
			$r->renderResponse('delete succeeded',false,200);
		} else {
			$r->renderError(403,'cannot delete collection with more than 5 items');
		}
	}

	public function getCollection($r) 
	{
		$tpl = new Dase_Template($r);
		$tpl->assign('collection',Dase_Atom_Feed::retrieve(APP_ROOT.'/collection/'.$r->get('collection_ascii_id').'.atom'));
		$r->renderResponse($tpl->fetch('collection/browse.tpl'));
	}

	public function getServiceAtom($r) 
	{
		$r->renderResponse($this->collection->getAtompubServiceDoc(),'application/atomsvc+xml');
	}


	public function postToAttributes($r) 
	{
		$user = $r->getUser('http');
		if (!$user->can('write',$this->collection)) {
			$r->renderError(401,'no go unauthorized');
		}
		$content_type = $r->getContentType();

		if ('application/atom+xml;type=entry' == $content_type ||
		'application/atom+xml' == $content_type ) {
			$this->_newAtomAttribute($r);
		} elseif ('application/json' == $content_type) {
			$this->_newJsonAttribute($r);
		} else {
			$r->renderError(415,'cannot accept '.$content_type);
		}
	}

	public function postToCollection($r) 
	{
		$user = $r->getUser('http');
		if (!$user->can('write',$this->collection)) {
			$r->renderError(401,'no go unauthorized');
		}
		$content_type = $r->getContentType();

		if ('application/atom+xml;type=entry' == $content_type ||
		'application/atom+xml' == $content_type ) {
			$this->_newAtomItem($r);
		} elseif ('application/json' == $content_type) {
			$this->_newJsonItem($r);
		} else {
			$r->renderError(415,'cannot accept '.$content_type);
		}
	}

	public function postToIngester($r) 
	{
		$user = $r->getUser('http');
		if (!$user->can('write',$this->collection)) {
			$r->renderError(401,'no go unauthorized');
		}
		$content_type = $r->getContentType();

		if ('application/atom+xml;type=entry' == $content_type ||
		'application/atom+xml' == $content_type ) {
			//will try to fetch enclosure
			$this->_newAtomItem($r,true);
		} else {
			$r->renderError(415,'cannot accept '.$content_type);
		}
	}

	private function _newAtomItem($r,$fetch_enclosure=false)
	{
		$raw_input = file_get_contents("php://input");
		$client_md5 = $r->getHeader('Content-MD5');
		//if Content-MD5 header isn't set, we just won't check
		if ($client_md5 && md5($raw_input) != $client_md5) {
			$r->renderError(412,'md5 does not match');
		}
		$item_entry = Dase_Atom_Entry::load($raw_input);
		if ('item' != $item_entry->entrytype) {
			$item_entry->setEntryType('item');
			$r->renderError(400,'must be an item entry');
		}
		//slug or title will be serial number
		if ( isset( $_SERVER['HTTP_SLUG'] ) ) {
			$r->set('serial_number',$_SERVER['HTTP_SLUG']);
		} elseif ( isset( $_SERVER['HTTP_TITLE'] ) ) {
			$r->set('serial_number',$_SERVER['HTTP_TITLE']);
		}
		try {
			$item = $item_entry->insert($r,$fetch_enclosure);
			header("HTTP/1.1 201 Created");
			header("Content-Type: application/atom+xml;type=entry;charset='utf-8'");
			header("Location: ".APP_ROOT."/item/".$r->get('collection_ascii_id')."/".$item->serial_number.'.atom');
			echo $item->asAtomEntry();
			exit;
		} catch (Dase_Exception $e) {
			$r->renderError(409,$e->getMessage());
		}
	}

	private function _newAtomAttribute($r)
	{
		$raw_input = file_get_contents("php://input");
		$client_md5 = $r->getHeader('Content-MD5');
		//if Content-MD5 header isn't set, we just won't check
		if ($client_md5 && md5($raw_input) != $client_md5) {
			$r->renderError(412,'md5 does not match');
		}
		$att_entry = Dase_Atom_Entry::load($raw_input);
		if ('attribute' != $att_entry->entrytype) {
			$att_entry->setEntryType('attribute');
			$r->renderError(400,'must be an attribute entry');
		}
		try {
			$att = $att_entry->insert($r,$this->collection);
			header("HTTP/1.1 201 Created");
			header("Content-Type: application/atom+xml;type=entry;charset='utf-8'");
			header("Location: ".APP_ROOT."/attribute/".$r->get('collection_ascii_id')."/".$att->ascii_id.'.atom');
			echo $att->asAtomEntry();
			exit;
		} catch (Dase_Exception $e) {
			$r->renderError(409,$e->getMessage());
		}
	}

	private function _newJsonItem($r)
	{
		$r->renderResponse('still working on JSON posts!');
	}

	private function _newJsonAttribute($r)
	{
		$r->renderResponse('still working on Attribute posts!');
	}

	public function rebuildIndexes($r) 
	{
		$c = Dase_Collection::get($r->get('collection_ascii_id'));
		$c->buildSearchIndex();
		$params['msg'] = "rebuilt indexes for $c->collection_name";
		$r->renderRedirect('',$params);
	}

	public function getAttributesAtom($r) 
	{
		$r->renderResponse($this->collection->getAttributesAtom()->asXml());
	}

	public function getAttributesJson($r) 
	{
		$filter = $r->has('filter') ? $r->get('filter') : '';
		$r->checkCache();
		$c = $this->collection;
		$attributes = new Dase_DBO_Attribute;
		$attributes->collection_id = $c->id;
		if ('public' == $filter) {
			$attributes->is_public = true;
		}
		if ('admin' == $filter) {
			$attributes->collection_id = 0;
		}
		if ($r->has('sort')) {
			$so = $r->get('sort');
		} else {
			$so = 'sort_order';
		}
		$attributes->orderBy($so);
		//$attributes->orderBy('attribute_name');
		$att_array = array();
		foreach($attributes->find() as $att) {
			$att_array[] =
				array(
					'id' => $att->id,
					'ascii_id' => $att->ascii_id,
					'attribute_name' => $att->attribute_name,
					'input_type' => $att->html_input_type,
					'sort_order' => $att->sort_order,
					'collection' => $r->get('collection_ascii_id')
				);
		}
		$r->renderResponse(Dase_Json::get($att_array),$r);
	}

	public function getAttributeTalliesJson($r) 
	{
		$prefix = Dase_Config::get('table_prefix');
		//todo: work on cacheing here
		//$r->checkCache(1500);
		if ($r->has('filter') && ('admin' == $r->get('filter'))) {
			$r->renderResponse(Dase_Json::get($this->_adminAttributeTalliesJson()));
			exit;
		}
		$c = $this->collection;
		$sql = "
			SELECT id, ascii_id
			FROM {$prefix}attribute a
			WHERE a.collection_id = ?
			AND a.is_public = true;
		";
		$st = Dase_DBO::query($sql,array($c->id));
		$sql = "
			SELECT count(DISTINCT value_text) 
			FROM {$prefix}value 
			WHERE attribute_id = ?";
		$db = Dase_DB::get();
		$sth = $db->prepare($sql);
		$tallies = array();
		while ($row = $st->fetch()) {
			$sth->execute(array($row['id']));
			$tallies[$row['ascii_id']] = $sth->fetchColumn();
		}
		$result['tallies'] = $tallies;
		$result['is_admin'] = 0;
		$r->renderResponse(Dase_Json::get($result));
	}

	private function _adminAttributeTalliesJson() 
	{
		$prefix = Dase_Config::get('table_prefix');
		$c = $this->collection;
		$sql = "
			SELECT id, ascii_id
			FROM {$prefix}attribute a
			WHERE a.collection_id = 0
			";
		$st = Dase_DBO::query($sql);
		$sql = "
			SELECT count(DISTINCT value_text) 
			FROM {$prefix}value v 
			WHERE v.attribute_id = ? 
			AND v.item_id IN
			(SELECT id FROM {$prefix}item i
			WHERE i.collection_id = $c->id)
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

	public function itemsByTypeAsAtom($r) {
		$item_type = new Dase_DBO_ItemType;
		$item_type->ascii_id = $r->get('item_type_ascii_id');
		$item_type->findOne();
		$r->renderResponse($item_type->getItemsAsFeed());
	}

	public function buildIndex($r) 
	{
		$c = Dase_Collection::get($r->get('collection_ascii_id'));
		$c->buildSearchIndex();
		$params['msg'] = "rebuilt indexes for $c->collection_name";
		$r->renderRedirect('',$params);
	}

	public function getServiceTxt($r)
	{
		$this->getService($r);
	}

	public function getService($r)
	{
		$r->response_mime_type = 'application/atomsvc+xml';
		$r->renderResponse($this->collection->getAtompubServiceDoc());
	}
}

