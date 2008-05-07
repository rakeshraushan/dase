<?php

class CollectionHandler
{
	public static function asAtom($params) 
	{
		$c = Dase_Collection::get($params);
		Dase::display($c->asAtom());
	}

	public static function asAtomArchive($params) 
	{
		$c = Dase_Collection::get($params);
		$limit = Dase_Filter::filterGet('limit');
		Dase::display($c->asAtomArchive($limit));
	}

	public static function asAtomFull($params) 
	{
		$c = Dase_Collection::get($params);
		Dase::display($c->asAtomFull());
	}

	public static function listAsAtom($params) 
	{
		if (Dase_Filter::filterGet('get_all')) {
			$public_only = false;
		} else {
			$public_only = true;
		}
		Dase::display(Dase_DBO_Collection::listAsAtom($public_only));
	}

	public static function browse($params) 
	{
		$tpl = new Dase_Template();
		$tpl->assign('collection',Dase_Atom_Feed::retrieve(DASE_URL.'/atom/collection/'.$params['collection_ascii_id']));
		Dase::display($tpl->fetch('collection/browse.tpl'));
	}

	public static function listAll($params) 
	{
		$tpl = new Dase_Template();
		$feed = Dase_Atom_Feed::retrieve(DASE_URL.'/atom');
		//$er = error_reporting(E_ALL^E_NOTICE);
		$er = error_reporting(E_ERROR);
		if ($feed->validate()) {
			//print "valid!";
		} else {
			print "not valid!";
			exit;
		}
		error_reporting($er);

		$tpl->assign('collections',$feed);
		//$tpl->assign('collections',Dase_Atom_Feed::retrieve(DASE_URL.'/atom'));
		Dase::display($tpl->fetch('collection/list.tpl'));
	}

	public static function itemTalliesAsJson($params) 
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
		Dase::display(Dase_Json::get($tallies));
	}

	public static function rebuildIndexes($params) 
	{
		$c = Dase_Collection::get($params);
		$c->buildSearchIndex();
		Dase::redirect('',"rebuilt indexes for $c->collection_name");
	}

	public static function attributesAsAtom($params) 
	{
		$c = Dase_Collection::get($params);
		$atts = new Dase_DBO_Attribute;
		$atts->collection_id = $c->id;
		$atts->is_public = 1;
		$atts->orderBy('sort_order');
		foreach ($atts->find() as $attribute) {
		}
		Dase::display();
	}

	public static function attributesAsHtml($params) 
	{
		$c = Dase_Collection::get($params);
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
		Dase::display($html);
	}

	public static function attributesAsJson($params) 
	{
		$c = Dase_Collection::get($params);
		$attribute = new Dase_DBO_Attribute;
		$attribute->collection_id = $c->id;
		$attribute->is_public = true;
		$attribute->orderBy('sort_order');
		$att_array = array();
		foreach($attribute->find() as $att) {
			$att_array[] =
				array(
					'id' => $att->id,
					'ascii_id' => $att->ascii_id,
					'attribute_name' => $att->attribute_name,
					'collection' => $params['collection_ascii_id']
				);
		}
		Dase::display(Dase_Json::get($att_array));
	}

	public static function adminAttributesAsHtml($params) 
	{
		$c = Dase_Collection::get($params);
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
		Dase::display($html);
	}

	public static function attributeTalliesAsJson($params) 
	{
		$c = Dase_Collection::get($params);
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
		Dase::display(Dase_Json::get($tallies));

	}

	public static function adminAttributeTalliesAsJson($params) 
	{
		$c = Dase_Collection::get($params);
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
		Dase::display(Dase_Json::get($tallies));

	}

	public static function itemsByTypeAsAtom($params) {
		$item_type = new Dase_DBO_ItemType;
		$item_type->ascii_id = $params['item_type_ascii_id'];
		$item_type->findOne();
		Dase::display($item_type->getItemsAsFeed());
	}

	public static function buildIndex($params) 
	{
		$c = Dase_Collection::get($params);
		$c->buildSearchIndex();
		Dase::redirect('',"rebuilt indexes for $c->collection_name");
	}
}

