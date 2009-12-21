<?php

class Dase_Handler_ItemType extends Dase_Handler
{
	public $type;
	public $resource_map = array(
		'/' => 'index',
		'{collection_ascii_id}/{item_type_ascii_id}' => 'item_type',
		'{collection_ascii_id}/{item_type_ascii_id}/item/{serial_number}' => 'item',
		'{collection_ascii_id}/{item_type_ascii_id}/item/{serial_number}/content' => 'content',
		//usually retrieved as app:categories
		'{collection_ascii_id}/{item_type_ascii_id}/items' => 'item_type_items',
		'{collection_ascii_id}/{item_type_ascii_id}/items/{created_by_eid}' => 'item_type_items',
		'{collection_ascii_id}/{item_type_ascii_id}/{att_ascii_id}/values' => 'values_list',
		'{collection_ascii_id}/{item_type_ascii_id}/service' => 'service',
		'{collection_ascii_id}/{item_type_ascii_id}/attributes' => 'attributes',
	);

	protected function setup($r)
	{
		$this->type = Dase_DBO_ItemType::get($this->db,$r->get('collection_ascii_id'),$r->get('item_type_ascii_id'));
		if (!$this->type) {
			$r->renderError(404);
		}
	}

	public function getValuesListJson($r)
	{
		$it_ascii = $r->get('item_type_ascii_id');
		$att_ascii = $r->get('att_ascii_id');
		$coll = $r->get('collection_ascii_id');
		$prefix = $r->retrieve('db')->table_prefix;
		$sql = "
			SELECT v.value_text,i.serial_number
			FROM {$prefix}value v,{$prefix}collection c,{$prefix}item i, {$prefix}attribute a, {$prefix}item_type it 
			WHERE it.ascii_id = ?
			AND c.ascii_id = ?
			AND a.ascii_id = ?
			AND v.attribute_id = a.id
			AND it.collection_id = c.id
			AND a.collection_id = c.id
			AND v.item_id = i.id
			AND i.collection_id = c.id
			AND i.item_type_id = it.id
			";
		if ($r->get('public_only')) {
			$sql .= " AND i.status = 'public' ";
		}
		$data = array();
		foreach (Dase_DBO::query($this->db,$sql,array($it_ascii,$coll,$att_ascii)) as $row) {
			$item_url = $r->app_root.'/item/'.$coll.'/'.$row['serial_number'];
			$data[$item_url] = $row['value_text'];
		}
		if (count($data)) {
			asort($data);
			$r->renderResponse(Dase_Json::get($data));
		} else {
			$r->renderError('404','no values');
		}

	}

	public function getIndex($r) {
		$r->renderResponse('greetings earth person');
	}

	public function getItemAtom($r)
	{
		$item = Dase_DBO_Item::get($this->db,$r->get('collection_ascii_id'),$r->get('serial_number'));
		if ($item) {
			$r->renderResponse($item->asAtomEntry($r->app_root));
		} else {
			$r->renderError(404);
		}
	}

	public function getItemJson($r)
	{
		$r->renderResponse(Dase_DBO_Item::get($this->db,$r->get('collection_ascii_id'),$r->get('serial_number'))->asAtomJson($r->app_root));
	}

	public function deleteItem($r)
	{
		$user = $r->getUser('service');
		$item = Dase_DBO_Item::get($this->db,$r->get('collection_ascii_id'),$r->get('serial_number'));
		if (!$user->can('write',$item)) {
			$r->renderError(401,'user cannot delete this item');
		}
		try {
			$item->expunge();
			$r->renderOk('item deleted');
		} catch (Exception $e) {
			$r->renderError(500);
		}
	}

	public function getItemType($r)
	{
		$r->renderResponse($this->type->name);
	}

	public function getItemTypeJson($r)
	{
		$items_array = array();
		$res = array();
		$coll = $r->get('collection_ascii_id');
		$items = new Dase_DBO_Item($this->db);
		$items->status = 'public';
		$items->item_type_id = $this->type->id;
		$items->orderBy('updated DESC');
		//can filter by author
		if ($r->has('created_by_eid')) {
			$items->created_by_eid = $r->get('created_by_eid');
		}
		foreach ($items->find() as $item) {
			$item_array  = array(
				'url' => $item->getUrl($r->app_root),
				//expensive??
				'title' => $item->getTitle(),
				'serial_number' => $item->serial_number,
			);
			$items_array[] = $item_array;
		}
		$res['items'] = $items_array;
		$res['name'] =  $this->type->name;
		$r->renderResponse(Dase_Json::get($res));
	}

	public function getItemTypeAtom($r)
	{
		$r->renderResponse($this->type->asAtomEntry($r->get('collection_ascii_id'),$r->app_root));
	}

	public function getAttributesAtom($r)
	{
		$r->renderResponse($this->type->getAttributesFeed($r->get('collection_ascii_id'),$r->app_root));
	}

	public function getAttributesJson($r)
	{
		$r->renderResponse($this->type->getAttributesJson($r->get('collection_ascii_id'),$r->app_root));
	}

	public function getService($r)
	{
		$r->response_mime_type = 'application/atomsvc+xml';
		$r->renderResponse($this->type->getAtompubServiceDoc($r->app_root));
	}

	public function getItemTypeItemsAtom($r)
	{
		$c = Dase_DBO_Collection::get($this->db,$r->get('collection_ascii_id'));
		$t = $this->type;
		$feed = new Dase_Atom_Feed;
		$feed->setId($t->getUrl($c->ascii_id,$r->app_root));
		$feed->setTitle($t->name.' Items');
		$items = new Dase_DBO_Item($this->db);
		$items->item_type_id = $t->id;
		$items->orderBy('updated DESC');
		//can filter by author
		if ($r->has('created_by_eid')) {
			$items->created_by_eid = $r->get('created_by_eid');
		}
		foreach ($items->find() as $item) {
			$feed->addItemEntry($item,$r->app_root);
		}
		if ($r->has('sort')) {
			$feed->sortBy($r->get('sort'));
		}
		$r->renderResponse($feed->asXml());
	}

	public function getItemTypeItemsJson($r)
	{
		$res = array();
		$coll = $r->get('collection_ascii_id');
		$items = new Dase_DBO_Item($this->db);
		$items->status = 'public';
		$items->item_type_id = $this->type->id;
		$items->orderBy('updated DESC');
		//can filter by author
		if ($r->has('created_by_eid')) {
			$items->created_by_eid = $r->get('created_by_eid');
		}
		foreach ($items->find() as $item) {
			$item_array  = array(
				'url' => $item->getUrl($r->app_root),
				//expensive??
				'title' => $item->getTitle(),
				'serial_number' => $item->serial_number,
			);
			$res[] = $item_array;
		}
		$r->renderResponse(Dase_Json::get($res));
	}

	public function putItem($r) 
	{
		$item = Dase_DBO_Item::get($this->db,$r->get('collection_ascii_id'),$r->get('serial_number'));
		try {
			$item_handler = new Dase_Handler_Item($this->db,$r->retrieve('config'));
			$item_handler->item = $item;
			$item_handler->putItem($r);
		} catch (Exception $e) {
			$r->renderError(500,$e->getMessage());
		}
		//if something goes wrong and control returns here
		$r->renderError(500,'error in put item (item type)');
	}

	/** this is used to UPDATE an item's content */
	public function postToContent($r)
	{
		$user = $r->getUser();
		$this->_updateContent($r,$user);
	}

	/** this is used to UPDATE an item's content */
	public function putContent($r)
	{
		//does this need to be service?? maybe 'http' is ok
		$user = $r->getUser('service');
		$this->_updateContent($r,$user);
	}

	private function _updateContent($r,$user)
	{
		$item = Dase_DBO_Item::get($this->db,$r->get('collection_ascii_id'),$r->get('serial_number'));
		if (!$user->can('write',$item)) {
			$r->renderError(401,'cannot write to this item');
		}
		$content_type = $r->getContentType();
		if ('application/x-www-form-urlencoded' == $content_type) {
			$content_type = 'text';
			$content = $r->get('content');
		} else {
		//todo: filter this!
			$content = $r->getBody();
		}
		if ($item->setContent($content,$user->eid,$content_type)) {
			$r->renderResponse('content updated');
		}
	}

	/** this is for ajax retrieval of content versions */
	public function getContentJson($r)
	{
		$user = $r->getUser();
		$item = Dase_DBO_Item::get($this->db,$r->get('collection_ascii_id'),$r->get('serial_number'));
		if (!$user->can('read',$item)) {
			$r->renderError(401,'user cannot read this item');
		}
		$r->renderResponse($item->getContentJson());
	}

	/** this is for simply getting the content 
	 * note that type MUST be a mime_type
	 * */
	public function getContent($r)
	{
		$user = $r->getUser();
		$item = Dase_DBO_Item::get($this->db,$r->get('collection_ascii_id'),$r->get('serial_number'));
		if (!$user->can('read',$item)) {
			$r->renderError(401,'user cannot read this item');
		}
		$cont = $item->getContents();	
		if ('xhtml' == $cont->type) {
			$mime_type = 'application/xhtml+xml';
		} elseif ('html' == $cont->type) {
			$mime_type = 'text/html';
		} elseif ('text' == $cont->type) {
			$mime_type = 'text/plain';
		} else {
			$mime_type = $cont->type;
		}
		$r->response_mime_type = $mime_type;
		$r->renderResponse($cont->text);
	}

}
