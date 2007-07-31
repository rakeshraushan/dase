<?php
include (DASE_CONFIG); 
if (!in_array(Dase::getUser()->eid,$conf['superusers'])) {
	Dase::reload('error','No dice...you need to be a superuser to go there.');
}

if (isset($params[0])) {
	$coll = new Dase_DB_Collection;
	$coll->ascii_id = $params[0];
	$coll->findOne();
	$coll->buildSearchIndex();
}
Dase::reload('admin',"rebuilt indexes for $coll->collection_name");
exit;
