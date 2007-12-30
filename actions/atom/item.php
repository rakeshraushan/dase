<?php

if (isset($params['collection_ascii_id']) && ($params['serial_number'])) {
	$item = Dase_Item::get($params['collection_ascii_id'],$params['serial_number']);
	if ($item) {
		Dase::display($item->asAtom(true));
	}
}
Dase::error(404);
