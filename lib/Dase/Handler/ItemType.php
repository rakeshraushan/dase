<?php

class Dase_Handler_ItemType extends Dase_Handler
{
	public $type;
	public $resource_map = array(
		'/' => 'index',
		'{collection_ascii_id}/{item_type_ascii_id}' => 'item_type',
		'{collection_ascii_id}/{item_type_ascii_id}/item/{serial_number}' => 'item',
		//usually retrieved as app:categories
		'{collection_ascii_id}/{item_type_ascii_id}/items' => 'item_type_items',
		'{collection_ascii_id}/{item_type_ascii_id}/items/{created_by_eid}' => 'item_type_items',
		'{collection_ascii_id}/{item_type_ascii_id}/{att_ascii_id}/values' => 'values_list',
		'{collection_ascii_id}/{item_type_ascii_id}/service' => 'service',
		'{collection_ascii_id}/{item_type_ascii_id}/attributes' => 'attributes',
		//usually retrieved as atom:feed
		'{collection_ascii_id}/{item_type_ascii_id}/children_of/{parent_type_ascii_id}/{parent_serial_number}' => 'related_item_type_items',
		'{collection_ascii_id}/{child_type_ascii_id}/children_of/{item_type_ascii_id}' => 'relation',
	);

	protected function setup($r)
	{
		$this->type = Dase_DBO_ItemType::get($r->get('collection_ascii_id'),$r->get('item_type_ascii_id'));
		if (!$this->type) {
			$r->renderError(404);
		}
	}

	public function getValuesListJson($r)
	{
		$app_root = Dase_Config::get('app_root');
		$it_ascii = $r->get('item_type_ascii_id');
		$att_ascii = $r->get('att_ascii_id');
		$coll = $r->get('collection_ascii_id');
		$prefix = Dase_Config::get('table_prefix');
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
		foreach (Dase_DBO::query($sql,array($it_ascii,$coll,$att_ascii)) as $row) {
			$item_url = $app_root.'/item/'.$coll.'/'.$row['serial_number'];
			$data[$item_url] = $row['value_text'];
		}
		asort($data);
		$r->renderResponse(Dase_Json::get($data));

	}

	public function getIndex($r) {
		$r->renderResponse('greetings earth person');
	}

	public function getItemAtom($r)
	{
		$item = Dase_DBO_Item::get($r->get('collection_ascii_id'),$r->get('serial_number'));
		if ($item) {
			$r->renderResponse($item->asAtomEntry());
		} else {
			$r->renderError(404);
		}
	}

	public function getItemJson($r)
	{
		$r->renderResponse(Dase_DBO_Item::get($r->get('collection_ascii_id'),$r->get('serial_number'))->asAtomJson());
	}

	public function deleteItem($r)
	{
		$user = $r->getUser('service');
		$item = Dase_DBO_Item::get($r->get('collection_ascii_id'),$r->get('serial_number'));
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

	public function getItemTypeAtom($r)
	{
		$r->renderResponse($this->type->asAtomEntry());
	}

	public function getAttributesAtom($r)
	{
		$r->renderResponse($this->type->getAttributesFeed());
	}

	public function getAttributesJson($r)
	{
		$r->renderResponse($this->type->getAttributesJson());
	}

	public function getService($r)
	{
		$r->response_mime_type = 'application/atomsvc+xml';
		$r->renderResponse($this->type->getAtompubServiceDoc());
	}

	public function getRelationChildren($r)
	{
		$this->getRelationChildrenAtom($r);
	}

	public function getItemTypeItemsAtom($r)
	{
		$app_root = Dase_Config::get('app_root');
		$c = Dase_DBO_Collection::get($r->get('collection_ascii_id'));
		$t = $this->type;
		$feed = new Dase_Atom_Feed;
		$feed->setId($app_root.'/'.$t->getRelativeUrl($c->ascii_id));
		$feed->setTitle($t->name.' Items');
		$items = new Dase_DBO_Item;
		$items->item_type_id = $t->id;
		$items->orderBy('updated DESC');
		//can filter by author
		if ($r->has('created_by_eid')) {
			$items->created_by_eid = $r->get('created_by_eid');
		}
		foreach ($items->find() as $item) {
			$feed->addItemEntry($item,$c);
		}
		if ($r->has('sort')) {
			$feed->sortBy($r->get('sort'));
		}
		$r->renderResponse($feed->asXml());
	}

	public function getItemTypeItemsJson($r)
	{
		$app_root = Dase_Config::get('app_root');
		$res = array();
		$coll = $r->get('collection_ascii_id');
		$items = new Dase_DBO_Item;
		$items->status = 'public';
		$items->item_type_id = $this->type->id;
		$items->orderBy('updated DESC');
		//can filter by author
		if ($r->has('created_by_eid')) {
			$items->created_by_eid = $r->get('created_by_eid');
		}
		foreach ($items->find() as $item) {
			$item_array  = array(
				'url' => $app_root.'/'.$item->getRelativeUrl($coll),
				//expensive??
				'title' => $item->getTitle(),
				'serial_number' => $item->serial_number,
			);
			$res[] = $item_array;
		}
		$r->renderResponse(Dase_Json::get($res));
	}

	public function getRelatedItemTypeItemsJson($r)
	{
		//he we are getting (child) item_type items
		//which have are related to parent item_type item
		//specified
		$items = array();
		$prefix = Dase_Config::get('table_prefix');
		$sql = "
			SELECT ir.child_serial_number
			FROM {$prefix}item_relation ir, {$prefix}item_type_relation itr
			WHERE ir.collection_ascii_id = ?
			AND itr.id = ir.item_type_relation_id
			AND ir.parent_serial_number = ?
			AND itr.child_type_ascii_id = ?
			AND itr.parent_type_ascii_id = ?
			";
		$bound = array(
			$r->get('collection_ascii_id'),
			$r->get('parent_serial_number'),
			$r->get('item_type_ascii_id'),
			$r->get('parent_type_ascii_id'),
		);
		$st = Dase_DBO::query($sql,$bound);
		while ($sernum = $st->fetchColumn()) {
			$item = Dase_DBO_Item::get($r->get('collection_ascii_id'),$sernum);
			$items[$sernum] = $item->asArray();
		}
		$r->renderResponse(Dase_Json::get($items));
	}

	public function getRelatedItemTypeItemsAtom($r)
	{
		//he we are getting (child) item_type items
		//which have are related to parent item_type item
		//specified
		$prefix = Dase_Config::get('table_prefix');
		$sql = "
			SELECT ir.child_serial_number
			FROM {$prefix}item_relation ir, {$prefix}item_type_relation itr
			WHERE ir.collection_ascii_id = ?
			AND itr.id = ir.item_type_relation_id
			AND ir.parent_serial_number = ?
			AND itr.child_type_ascii_id = ?
			AND itr.parent_type_ascii_id = ?
			";
		$bound = array(
			$r->get('collection_ascii_id'),
			$r->get('parent_serial_number'),
			$r->get('item_type_ascii_id'),
			$r->get('parent_type_ascii_id'),
		);
		$st = Dase_DBO::query($sql,$bound);
		$feed = new Dase_Atom_Feed;
		$feed->setId(APP_ROOT.$r->getUrl());
		$feed->updated = date(DATE_ATOM);
		$feed->setTitle('feed of '.$this->type->name.' child entries for item '.$r->get('collection_ascii_id').'/'.$r->get('parent_serial_number'));
		while ($sernum = $st->fetchColumn()) {
			$item = Dase_DBO_Item::get($r->get('collection_ascii_id'),$sernum);
			$entry = $feed->addEntry();
			$item->injectAtomEntryData($entry);
			//todo: need to override updated and author here??
		}
		$r->renderResponse($feed->asXml());
	}

	public function getRelationAtom($r) 
	{
		$c = Dase_DBO_Collection::get($r->get('collection_ascii_id'));
		if (!$c) {
			$r->renderError(401);
		}
		$parent = $this->type;
		$child = Dase_DBO_ItemType::get($c->ascii_id,$r->get('child_type_ascii_id'));
		if (!$parent || !$child) {
			$r->renderError(401);
		}
		$rel = new Dase_DBO_ItemTypeRelation;
		$rel->collection_ascii_id = $r->get('collection_ascii_id');
		$rel->parent_type_ascii_id = $parent->ascii_id;
		$rel->child_type_ascii_id = $child->ascii_id;
		if (!$rel->findOne()) {
			$r->renderError(404);
		}
		$r->renderResponse($rel->asAtomEntry());
	}

	public function postToRelations($r)
	{
		//todo: implement this
	}

	public function putItem($r) 
	{
		$item = Dase_DBO_Item::get($r->get('collection_ascii_id'),$r->get('serial_number'));
		try {
			$item_handler = new Dase_Handler_Item;
			$item_handler->item = $item;
			$item_handler->putItem($r);
		} catch (Exception $e) {
			$r->renderError(500,$e->getMessage());
		}
		//if something goes wrong and control returns here
		$r->renderError(500,'error in put item (item type)');
	}
}
