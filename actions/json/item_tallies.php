<?php
/**
 * reimplement cache not just for xml
 */
/*
$cached_xml = Dase_DB_XmlCache::getXml('item_tallies');
if ($cached_xml) {
	$tpl = new Dase_Xml_Template;
	$tpl->setXml($cached_xml);
	$tpl->display();
	exit;
}
 */
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

$dj = new Dase_Json;
$tpl = new Dase_Json_Template;
$tpl->setJson($dj->encodeData($tallies));
$tpl->display();
