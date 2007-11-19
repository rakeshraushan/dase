<?php

if (isset($params['collection_ascii_id']) && ($params['serial_number'])) {
	$item_xml = ''; 
	$cache = new Dase_FileCache($params['collection_ascii_id'] . '_' . $params['serial_number'] . '.atom');
	if ($cache->get()) {
		$item_xml = $cache->get();
	} else {
		$t = new Dase_Xslt(XSLT_PATH.'/atom/item.xsl',XSLT_PATH.'/atom/layout.xml');
		$t->set('src',APP_ROOT.'/xml/' . $params['collection_ascii_id'] . '/' . $params['serial_number']);
		$tpl = new Dase_Xml_Template();
		$item_xml = $t->transform();
		$cache->set($item_xml);
	}
	$tpl = new Dase_Xml_Template();
	$tpl->setXml($item_xml);
	$tpl->setContentType('application/atom+xml');
	$tpl->display();
}
