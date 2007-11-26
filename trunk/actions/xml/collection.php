<?php

if (isset($params['collection_ascii_id'])) {
	$c = Dase_Collection::get($params['collection_ascii_id']);
	if ($c) {
		Dase::display($c->getXml());
	} 
}
Dase::error(404);
