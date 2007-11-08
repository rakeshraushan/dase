<?php
$cache = new Dase_FileCache('item_tallies');
if ($cache->get()) {
	$tallies = unserialize($cache->get());
} else {
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
	$cache->set(serialize($tallies));
}
$dj = new Dase_Json;
$tpl = new Dase_Json_Template;
$tpl->setJson($dj->encodeData($tallies));
$tpl->display();
