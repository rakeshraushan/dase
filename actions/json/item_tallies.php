<?php
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
