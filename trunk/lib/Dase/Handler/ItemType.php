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
		'{collection_ascii_id}/{item_type_ascii_id}/service' => 'service',
		'{collection_ascii_id}/{item_type_ascii_id}/attributes' => 'attributes',
		'{collection_ascii_id}/{item_type_ascii_id}/categories' => 'categories',
		//usually retrieved as atom:feed
		'{collection_ascii_id}/{item_type_ascii_id}/children_of/{parent_type_ascii_id}/{parent_serial_number}' => 'item_type_items',
		'{collection_ascii_id}/{item_type_ascii_id}/to/{child_type_ascii_id}' => 'relation',
	);

	protected function setup($r)
	{
		$this->type = Dase_DBO_ItemType::get($r->get('collection_ascii_id'),$r->get('item_type_ascii_id'));
		if (!$this->type) {
			$r->renderError(404);
		}
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

	public function getItemType($r)
	{
		$r->renderResponse($this->type->name);
	}

	public function getItemTypeAtom($r)
	{
		if ('feed' == $r->get('type')) {
			$r->renderResponse($this->type->getItemsAsFeed());
		} else {
			$r->renderResponse($this->type->asAtomEntry());
		}
	}

	public function getAttributesAtom($r)
	{
		$r->renderResponse($this->type->getAttributesFeed());
	}

	public function getAttributesCats($r)
	{
		$r->renderResponse($this->type->getAttributesAsCategories());
	}

	public function getAttributesJson($r)
	{
		$r->renderResponse($this->type->getAttributesJson());
	}

	public function getCategoriesCats($r)
	{
		$r->renderResponse($this->type->getAttributesAsCategories());
	}

	public function getCategoriesJson($r)
	{
		$r->renderResponse($this->type->getAttributesJson());
	}

	public function getItemTypeJson($r)
	{
		$res = array();
		$items = array();
		foreach ($this->type->getItems() as $it_obj) {
			$it['serial_number'] = $it_obj->serial_number;
			$it['title'] = $it_obj->getTitle();
			$items[] = $it;
		}
		$res['items'] = $items;
		$res['type']['name'] = $this->type->name;
		$res['type']['ascii_id'] = $this->type->ascii_id;
		$res['collection'] = $r->get('collection_ascii_id');
		$r->renderResponse(Dase_Json::get($res));
	}

	public function getItemTypeItemsCats($r)
	{
		$t = $this->type;
		$cats = new Dase_Atom_Categories;
		$cats->setScheme($t->getBaseUrl());
		foreach ($t->getItems(500) as $item) {
			$cats->addCategory($item->serial_number,'',$item->getTitle());
		}
		$r->renderResponse($cats->asXml());
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

	public function postToRelation($r) 
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
		$rel->parent_type_id = $parent->id;
		$rel->child_type_id = $child->id;
		if (!$rel->findOne()) {
			$r->renderError(404);
		}
		$rel->title = trim(file_get_contents("php://input"));
		$rel->update();
		$r->renderResponse('updated relation');
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

