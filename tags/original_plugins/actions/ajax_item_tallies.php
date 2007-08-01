<?php
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
