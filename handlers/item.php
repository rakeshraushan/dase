<?php

class ItemHandler
{
	public static function asAtom() {
		$params = Dase::instance()->params;
		if (isset($params['collection_ascii_id']) && ($params['serial_number'])) {
			$item = Dase_Item::get($params['collection_ascii_id'],$params['serial_number']);
			if ($item) {
				Dase::display($item->asAtom());
			}
		}
		Dase::error(404);
	}

	public static function display() {
		$params = Dase::instance()->params;
		if (isset($params['collection_ascii_id']) && ($params['serial_number'])) {
			//see if it exists
			if (Dase_Item::get($params['collection_ascii_id'],$params['serial_number'])) {
				$t = new Dase_Xslt;
				$t->stylesheet = XSLT_PATH.'item/transform.xsl';
				$t->set('src',APP_ROOT.'/atom/collection/'. $params['collection_ascii_id'] . '/' . $params['serial_number']);
				Dase::display($t->transform());
			} else {
				Dase::error(404);
			}
		}
	}
}

