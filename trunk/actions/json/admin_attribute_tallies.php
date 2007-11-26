<?php
$c = Dase::instance()->collection;
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
