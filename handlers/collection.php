<?php

class CollectionHandler extends Dase_Handler
{
	public $collection;
	public $resource_map = array(
		'{collection_ascii_id}' => 'collection',
		'{collection_ascii_id}/attributes' => 'attributes',
		'item_tallies' => 'itemtallies',
		"pk/{id}/{ddd}" => 'test',
	);

	public function setup($r)
	{
		if ($r->has('collection_ascii_id')) {
			$this->collection = Dase_DBO_Collection::get($r->get('collection_ascii_id'));
		}
	}

	public function getCollectionAtom($request) 
	{
		Dase::display($this->collection->asAtom(),$request);
	}

	public function asAtomArchive($r) 
	{
		$limit = $r->get('limit');
		Dase::display($this->collection->asAtomArchive($limit),$r);
	}

	public function asAtomFull($request) 
	{
		$c = Dase_Collection::get($request->get('collection_ascii_id'));
		Dase::display($c->asAtomFull(),$request);
	}

	public function getCollection($request) 
	{
		$tpl = new Dase_Template($request);
		$tpl->assign('collection',Dase_Atom_Feed::retrieve(DASE_URL.'/collection/'.$request->get('collection_ascii_id').'.atom'));
		Dase::display($tpl->fetch('collection/browse.tpl'),$request);
	}

	public function rebuildIndexes($request) 
	{
		$c = Dase_Collection::get($request->get('collection_ascii_id'));
		$c->buildSearchIndex();
		Dase::redirect('',"rebuilt indexes for $c->collection_name");
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
		Dase::display();
	}

	public function attributesAsHtml($request) 
	{
		$c = Dase_Collection::get($request->get('collection_ascii_id'));
		$atts = new Dase_DBO_Attribute;
		$atts->collection_id = $c->id;
		$atts->is_public = 1;
		$atts->orderBy('sort_order');
		$html ="<h4>Select Attribute:</h4>";
		$html .="<div id=\"get_public_tallies\" class=\"hide\"></div>";
		$html .="<ul id=\"attList\">";
		foreach ($atts->find() as $attribute) {
			$html .=<<<EOF
			<li><a href="collection/$c->ascii_id/attribute/$attribute->ascii_id" id="$attribute->ascii_id" class="att_link">$attribute->attribute_name <span class="tally" id="tally-{$attribute->ascii_id}"></span></a></li>
EOF;
		}
		$html .="</ul></div>";
		Dase::display($html,$request);
	}

	public function getAttributesJson($request) 
	{
		$c = $this->collection;
		$attributes = new Dase_DBO_Attribute;
		$attributes->collection_id = $c->id;
		$attributes->is_public = true;
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
		Dase::display(Dase_Json::get($att_array),$request);
	}

	public function adminAttributesAsHtml($request) 
	{
		$c = Dase_Collection::get($request->get('collection_ascii_id'));
		$atts = new Dase_DBO_Attribute;
		$atts->collection_id = 0;

		$html ="<h4 id=\"adminAttsLabel\" class=\"hide\">Select Admin Attribute:</h4>";
		$html .="<div id=\"get_admin_tallies\">Loading...getting tallies...</div>";
		$html .="<ul id=\"attList\" class=\"hide\">";

		foreach ($atts->find() as $attribute) {
			$html .=<<<EOF
			<li><a href="collection/$c->ascii_id/attribute/$attribute->ascii_id" id="$attribute->ascii_id" class="att_link">$attribute->attribute_name <span class="tally" id="tally-{$attribute->ascii_id}"></span></a></li>
EOF;
		}
		$html .="</ul></div>";
		Dase::display($html,$request);
	}

	public function attributeTalliesAsJson($request) 
	{
		$c = Dase_Collection::get($request->get('collection_ascii_id'));
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
		Dase::display(Dase_Json::get($tallies),$request);

	}

	public function adminAttributeTalliesAsJson($request) 
	{
		$c = Dase_Collection::get($request->get('collection_ascii_id'));
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
		Dase::display(Dase_Json::get($tallies),$request);

	}

	public function itemsByTypeAsAtom($request) {
		$item_type = new Dase_DBO_ItemType;
		$item_type->ascii_id = $request->get('item_type_ascii_id');
		$item_type->findOne();
		Dase::display($item_type->getItemsAsFeed(),$request);
	}

	public function buildIndex($request) 
	{
		$c = Dase_Collection::get($request->get('collection_ascii_id'));
		$c->buildSearchIndex();
		Dase::redirect('',"rebuilt indexes for $c->collection_name");
	}

	public function asJsonCollection($request) 
	{
		$page = $request->get('page');
		$c = Dase_Collection::get($request->get('collection_ascii_id'));
		Dase::display($c->asJsonCollection($page),$request);
	}
}

