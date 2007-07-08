<?php

class Dase_AjaxHandler 
{
	public static function index() {
		$tpl = new Dase_Xml_Template;
		$tpl->setXml('<empty/>');
		$tpl->display();
		exit;
	}

	public static function itemTallies() {
		$cached_xml = Dase_DB_XmlCache::getXml('item_tallies');
		if ($cached_xml) {
			$tpl = new Dase_Xml_Template;
			$tpl->setXml($cached_xml);
			$tpl->display();
			exit;
		}
		$db = Dase_DB::get();
		$sql = "
			select collection.id, collection.ascii_id,count(item.id) 
			as count
			from
			collection, item
			where collection.id = item.collection_id
			and item.status_id = 0
			group by collection.id, collection.ascii_id
			";
		$st = $db->query($sql);
		$dom = new DOMDocument('1.0');
		$root = $dom->appendChild($dom->createElement('collections'));
		foreach ($st->fetchAll() as $row) {
			$coll = $dom->createElement('collection');
			$coll = $root->appendChild($coll);
			$coll->setAttribute('id',$row['id']);
			$coll->setAttribute('item_tally',$row['count']);
			$coll->setAttribute('ascii_id',$row['ascii_id']);
		}
		$xml = $dom->saveXML();
		$tpl = new Dase_Xml_Template;
		$tpl->setXml($xml);
		Dase_DB_XmlCache::saveXml('item_tallies',$xml);
		$tpl->display();
		exit;
	}

	public static function attributesByCategory() {
		$link_class = Dase_Utils::filterGet('link_class');
		$collection_id = Dase_Utils::filterGet('collection_id');
		$cat_id = Dase_Utils::filterGet('cat_id');
		$public_only = Dase_Utils::filterGet('public_only');
		$token = Dase_Utils::filterGet('token');
		$cat = new Dase_DB_Category;

		if ($cat_id) {
			$cat->load($cat_id);
			$attribute = new Dase_DB_Attribute;
			$sql = "
				SELECT * FROM attribute
				WHERE id IN (
					SELECT attribute_id FROM attribute_category
					WHERE category_id = ?)
					AND is_public = ?
					";
			if ($public_only) {
				$attribute_array = $attribute->query($sql,array($cat_id,1));
			} else {
				$attribute_array = $attribute->query($sql,array($cat_id,0));
			}
		} else {
			$attribute = new Dase_DB_Attribute;
			$attribute->collection_id = $collection_id;
			if ($public_only) {
				$attribute->is_public = 1;
			}
			$attribute->orderBy('sort_order');
			$attribute_array = $attribute->findAll();
		}

		$html = "<div id=\"getTallies\">";
		$html .="<h4>Select <span class=\"attributeName\">$cat->name</span> Attribute:</h4>";
		$html .="<ul id=\"attList\">";

		if (is_array($attribute_array)) {
			foreach ($attribute_array as $attribute) {
				$html .=<<<EOF
			<li><a href="#" class="$link_class {$attribute['id']}" id="att_link_{$attribute['id']}">{$attribute['attribute_name']} <span class="tally" id="tally-{$attribute['id']}"></span></a></li>
EOF;
			}
		}
		$html .="</ul></div>";
		header('Content-Type: text/html; charset=utf-8');
		header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
		echo $html;
		exit;

	}

	public static function valuesByAttribute() {
		$attribute_id = Dase_Utils::filterGet('attribute_id');
		$collection_id = Dase_Utils::filterGet('collection_id');
		$att = new Dase_DB_Attribute;
		if ($attribute_id) {
			$att->load($attribute_id);
			$values_array = $att->getDisplayValues(400);
		}
		$html_coda = '</ul>';
		if (is_array($values_array) && (count($values_array) == 400)) {
			$html_coda = "<div class=\"alert\">Only the first 400 values are displayed.</div>";
		}
		$html ="<h4>Select <span class=\"attributeName\">$att->attribute_name</span> Value:</h4>";
		$html .="<ul>";

		if (is_array($values_array)) {
			foreach ($values_array as $value) {
				$urlenc = $value['urlencoded_value_text'];
				$text = $value['value_text'];
				$tally = $value['tally'];
				if (!strstr($value['value_text']," ") && (strlen($value['value_text']) > 50)) {
					$value['value_text'] = substr_replace($value['value_text'],'...',47);
				}
				$html .=<<<EOF
		<li><a href="index.php?action=search&query=$urlenc&collection_id=$collection_id&attribute_id=$attribute_id" class="val_link">$text <span class="tally">($tally)</span></a></li>
EOF;
			}
		}
		$html .= $html_coda;
		header('Content-Type: text/html; charset=utf-8');
		echo $html;
		exit;
	}

	public static function adminAttributes() {
		$link_class = Dase_Utils::filterGet('link_class');
		$collection_id = Dase_Utils::filterGet('collection_id');
		$token = Dase_Utils::filterGet('token');
		$attribute = new Dase_DB_Attribute;
		$attribute->collection_id = 0;
		$attribute_array = $attribute->findAll();

		$html = "<div class=\"adminAtts\" id=\"getTallies\">";
		$html .="<h4>Select Admin Attribute:</h4>";
		$html .="<ul id=\"attList\">";

		if (is_array($attribute_array)) {
			foreach ($attribute_array as $attribute) {
				$html .=<<<EOF
			<li><a href="#" class="$link_class {$attribute['id']} $collection_id" id="att_link_{$attribute['id']}">{$attribute['attribute_name']} <span class="tally" id="tally-{$attribute['id']}"></span></a></li>
EOF;
			}
		}
		$html .="</ul></div>";
		header('Content-Type: text/html; charset=utf-8');
		header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
		echo $html;
		exit;
	}

	public static function attributeTallies() {
		$cb = Dase_Utils::filterGet('cb');
		$admin = Dase_Utils::filterGet('admin');
		$collection_id = Dase_Utils::filterGet('coll_id');
		$cached_xml = Dase_DB_XmlCache::getXml('attribute_tallies',$collection_id,$admin);
		if ($cached_xml) {
			$tpl = new Dase_Xml_Template;
			$tpl->setXml($cached_xml);
			$tpl->display();
			exit;
		}
		if (!$cb) {
			$public = 'AND attribute.is_public = 1';
		} else {
			$public = '';
		}
		//XXXX bind parameter? will require st->execute() fix... 
		if ($admin) {
			$admin_sql = "AND value.item_id IN
				(SELECT id FROM item
				WHERE item.collection_id = $collection_id)
				";
			$collection_id = 0;
			$public = '';
		} else {
			$admin_sql = '';
		}
		/*
		$sql = "
			SELECT value.attribute_id, attribute.ascii_id, count(distinct value.value_text) as count
			FROM attribute,value,
			WHERE value.attribute_id = attribute.id
			$public
			$admin_sql
			AND attribute.collection_id = ?
			GROUP BY value.attribute_id
			";
		 */
		$sql = "
			SELECT id, ascii_id
			FROM attribute
			WHERE attribute.collection_id = ?
			$public
			";
		$db = Dase_DB::get();
		$st = $db->prepare($sql);	
		$st->execute(array($collection_id));

		$dom = new DOMDocument('1.0');
		$root = $dom->createElement('attributes');
		$dom->appendChild($root);
		$sql = "SELECT count(DISTINCT value_text) FROM value WHERE attribute_id = ? $admin_sql";
		$sth = $db->prepare($sql);
		while ($row = $st->fetch()) {
			$att = $dom->createElement('attribute');
			$att = $root->appendChild($att);
			$att->setAttribute('id',$row['id']);
			$att->setAttribute('ascii_id',$row['ascii_id']);
			$sth->execute(array($row['id']));
			$att->setAttribute('val_tally',$sth->fetchColumn());
		}
		$xml = $dom->saveXML();
		$tpl = new Dase_Xml_Template;
		$tpl->setXml($xml);
		Dase_DB_XmlCache::saveXml('attribute_tallies',$xml,$collection_id);
		$tpl->display();
		exit;
	}
}
