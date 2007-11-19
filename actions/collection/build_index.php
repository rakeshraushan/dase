<?php
if (isset($params['collection_ascii_id'])) {
	$coll = Dase_Collection::get($params['collection_ascii_id']);
	$coll->buildSearchIndex();
}
Dase::reload('',"rebuilt indexes for $coll->collection_name");
exit;
