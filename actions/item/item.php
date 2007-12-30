<?php

if (isset($params['collection_ascii_id']) && ($params['serial_number'])) {
	//see if it exists
	if (Dase_Item::get($params['collection_ascii_id'],$params['serial_number'])) {
		$t = new Dase_Xslt(XSLT_PATH.'item/default.xsl');
		$t->set('local-layout',XSLT_PATH.'item/default.xml');
		$t->set('src',APP_ROOT.'/atom/'. $params['collection_ascii_id'] . '/' . $params['serial_number']);
		Dase::display($t->transform());
	} else {
		Dase::error(404);
	}
}
