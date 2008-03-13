<?php

class CollectionHandler
{
	public static function asAtom() {
		$c = Dase_Registry::get('collection');
		if ($c) {
			Dase::display($c->asAtom());
		} 
		Dase_Error::report(404);
	}

	public static function listAsAtom() {
		if (Dase::filterGet('get_all')) {
			$public_only = false;
		} else {
			$public_only = true;
		}
		Dase::display(Dase_DB_Collection::listAsAtom($public_only));
	}

	public static function browse() {
		$params = Dase_Registry::get('params');
		$t = new Dase_Xslt;
		$t->stylesheet = XSLT_PATH.'collection/browse.xsl';
		$t->set('src',APP_ROOT. '/atom/collection/' . $params['collection_ascii_id']);
		Dase::display($t->transform());
	}

	public static function listAll() {
		$t = new Dase_Xslt;
		$t->stylesheet = XSLT_PATH.'collection/list.xsl';
		$t->set('src',APP_ROOT. '/atom');

		//xhtml output:
		Dase::display($t->transform());

		//html output:
		//$t2 = new Dase_Xslt;
		//$t2->stylesheet = XSLT_PATH.'xhtml2html.xsl;
		//$t2->source = $t->transform();
		//Dase::display($t2->transform());
	}

	public static function itemTalliesAsJson() {
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

	public static function rebuildIndexes() {
		$c = Dase_Registry::get('collection');
		if ($c) {
			$c->buildSearchIndex();
		} 
		Dase::redirect('',"rebuilt indexes for $coll->collection_name");
	}

	public static function attributesAsAtom() {
		$c = Dase_Registry::get('collection');
		$atts = new Dase_DB_Attribute;
		$atts->collection_id = $c->id;
		$atts->is_public = 1;
		$atts->orderBy('sort_order');
		foreach ($atts->find() as $attribute) {
		}
		Dase::display();
	}

	public static function attributesAsHtml() {
		$c = Dase_Registry::get('collection');
		$atts = new Dase_DB_Attribute;
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

	public static function attributesAsJson() {
		$params = Dase_Registry::get('params');
		$c = Dase_Registry::get('collection');
		$attribute = new Dase_DB_Attribute;
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

	public static function adminAttributesAsHtml() {
		$c = Dase_Registry::get('collection');
		$atts = new Dase_DB_Attribute;
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

	public static function attributeTalliesAsJson() {
		$c = Dase_Registry::get('collection');
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

	public static function adminAttributeTalliesAsJson() {
		$c = Dase_Registry::get('collection');
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

	public static function methodTemplate() {
		$c = Dase_Registry::get('collection');
		$params = Dase_Registry::get('params');

	}

	public static function buildIndex() {
		$c = Dase_Registry::get('collection');
		$c->buildSearchIndex();
		Dase::redirect('',"rebuilt indexes for $c->collection_name");
	}
}

