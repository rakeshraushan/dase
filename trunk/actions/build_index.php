<?php
if (isset($params['collection_ascii_id'])) {
	$coll = new Dase_DB_Collection;
	$coll->ascii_id = $params['collection_ascii_id'];
	$coll->findOne();
	$coll->buildSearchIndex();
}
Dase::reload('',"rebuilt indexes for $coll->collection_name");
exit;
