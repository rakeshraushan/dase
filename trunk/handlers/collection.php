<?php

class CollectionHandler extends Dase_Handler
{
	public $collection;
	public $resource_map = array(
		'{collection_ascii_id}' => 'collection',
		'{collection_ascii_id}/attributes' => 'attributes',
		'{collection_ascii_id}/attributes/tallies' => 'attribute_tallies',
		'{collection_ascii_id}/attributes/{filter}' => 'attributes',
		'{collection_ascii_id}/attributes/{filter}/tallies' => 'attribute_tallies',
		'{collection_ascii_id}/attribute/{att_ascii_id}/values' => 'attribute_values',
	);

	protected function setup($request)
	{
		if ($request->has('collection_ascii_id')) {
			$this->collection = Dase_DBO_Collection::get($request->get('collection_ascii_id'));
		}
	}

	public function getCollectionAtom($request) 
	{
		$request->renderResponse($this->collection->asAtom());
	}

	public function asAtomArchive($request) 
	{
		$limit = $request->get('limit');
		$request->renderResponse($this->collection->asAtomArchive($limit));
	}

	public function asAtomFull($request) 
	{
		$c = Dase_Collection::get($request->get('collection_ascii_id'));
		$request->renderResponse($c->asAtomFull());
	}

	public function getCollection($request) 
	{
		$tpl = new Dase_Template($request);
		$tpl->assign('collection',Dase_Atom_Feed::retrieve(DASE_URL.'/collection/'.$request->get('collection_ascii_id').'.atom'));
		$request->renderResponse($tpl->fetch('collection/browse.tpl'));
	}

	public function rebuildIndexes($request) 
	{
		$c = Dase_Collection::get($request->get('collection_ascii_id'));
		$c->buildSearchIndex();
		$request->renderRedirect('',"rebuilt indexes for $c->collection_name");
	}

	public function attributesAsAtom($request) 
	{
		$c = Dase_Collection::get($request->get('collection_ascii_id'));
		$atts = new Dase_DBO_Attribute;
		$atts->collection_id = $c->id;
		$atts->is_public = 1;
		$atts->orderBy('sort_order');
		foreach ($atts->find() as $attribute) {
		}
		//?????????????????????????????????????????????????????
		$request->renderResponse();
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
		$att_array = array();
		foreach($attributes->find() as $att) {
			$att_array[] =
				array(
					'id' => $att->id,
					'ascii_id' => $att->ascii_id,
					'attribute_name' => $att->attribute_name,
					'collection' => $request->get('collection_ascii_id')
				);
		}
		$request->renderResponse(Dase_Json::get($att_array),$request);
	}

	public function getAttributeTalliesJson($request) 
	{
		$request->checkCache(1500);
		if ($request->has('filter') && ('admin' == $request->get('filter'))) {
			$request->renderResponse($this->_adminAttributeTalliesJson());
			exit;
		}
		$c = $this->collection;
		$sql = "
			SELECT id, ascii_id
			FROM attribute
			WHERE attribute.collection_id = ?
			AND attribute.is_public = true;
		";
		$db = Dase_DB::get();
		$st = $db->prepare($sql);	
		$st->execute(array($c->id));
		$sql = "SELECT count(DISTINCT value_text) FROM value WHERE attribute_id = ?";
		$sth = $db->prepare($sql);
		$tallies = array();
		while ($row = $st->fetch()) {
			$sth->execute(array($row['id']));
			$tallies[$row['ascii_id']] = $sth->fetchColumn();
		}
		$request->renderResponse(Dase_Json::get($tallies));
	}

	private function _adminAttributeTalliesJson() 
	{
		$c = $this->collection;
		$sql = "
			SELECT id, ascii_id
			FROM attribute
			WHERE attribute.collection_id = 0
			";
		$db = Dase_DB::get();
		$st = $db->prepare($sql);	
		$st->execute();
		$sql = "
			SELECT count(DISTINCT value_text) 
			FROM value WHERE attribute_id = ? 
			AND value.item_id IN
			(SELECT id FROM item
			WHERE item.collection_id = $c->id)
			";
		$sth = $db->prepare($sql);
		$tallies = array();
		while ($row = $st->fetch()) {
			$sth->execute(array($row['id']));
			$tallies[$row['ascii_id']] = $sth->fetchColumn();
		}
		return $tallies;
	}

	public function getAttributeValuesJson($request) 
	{
		$attr = Dase_DBO_Attribute::get($request->get('collection_ascii_id'),$request->get('att_ascii_id'));
		if (0 == $attr->collection_id) {
			//since it is admin att we need to be able to limit to items in this coll
			$values_array = $attr->getDisplayValues($this->collection->ascii_id);
		} else {
			$values_array = $attr->getDisplayValues();
		}
		$request->renderResponse(Dase_Json::get($values_array));
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
		$request->renderRedirect('',"rebuilt indexes for $c->collection_name");
	}

	public function asJsonCollection($request) 
	{
		$page = $request->get('page');
		$c = Dase_Collection::get($request->get('collection_ascii_id'));
		$request->renderResponse($c->asJsonCollection($page));
	}
}

