<?php
$cb = Dase::filterGet('cb');
$admin = Dase::filterGet('admin');
$coll = Dase::filterGet('coll');
$c = Dase_DB_Collection::get($coll);
$collection_id = $c->id;
$cached_xml = Dase_DB_XmlCache::getXml('attribute_tallies',$collection_id,$admin);
if ($cached_xml) {
	$tpl = new Dase_Xml_Template;
	$tpl->setXml($cached_xml);
	$tpl->display();
	exit;
}
if (!$cb) {
	$public = 'AND attribute.is_public = true';
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
