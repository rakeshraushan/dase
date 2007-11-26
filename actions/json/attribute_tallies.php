<?php

$c = Dase::instance()->collection;
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
