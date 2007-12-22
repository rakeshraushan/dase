<?php
$tag_item = new Dase_DB_TagItem;
$tot = array();
$slice = array();
$top_ten = array();
foreach ($tag_item->getAll() as $t) {
	$tag_item = new Dase_DB_TagItem($t);
	if (isset($tot[$tag_item->p_collection_ascii_id])) {
		$tot[$tag_item->p_collection_ascii_id]++;
	} else {
		$tot[$tag_item->p_collection_ascii_id] = 1;
	}
}

arsort($tot);
$slice = array_slice($tot,0,20);

foreach ($slice as $k => $v) {
	$coll = Dase_DB_Collection::get($k);
	if ($coll) {
	$top_ten[$coll->collection_name] = $v;
	}
}

$db = Dase_DB::get();
$sql = "
	SELECT count(item.id), collection_name
	FROM item, collection
	WHERE item.collection_id = collection.id
	GROUP BY collection_name
	ORDER BY count DESC
	LIMIT 10
	";
$sth = $db->query($sql);
$sth->setFetchMode(PDO::FETCH_ASSOC);
$by_size = array();
while ($row = $sth->fetch()) {
	$by_size[$row['collection_name']] = $row['count'];
}


$tpl = new Smarty;
$tpl->assign('app_root',APP_ROOT);
$tpl->assign('breadcrumb_url','manage/stats');
$tpl->assign('breadcrumb_name','collection usage statics');
$tpl->assign('top_ten',$top_ten);
$tpl->assign('by_size',$by_size);
$tpl->display('manage/index.tpl');
exit;
