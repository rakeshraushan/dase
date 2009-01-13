<?php

/** this is mainly for one-off utility/maintenance scripts */

class Dase_Handler_Util extends Dase_Handler
{
	public $resource_map = array(
		'/' => 'index',
		'index' => 'index',
		'ica/badsernum' => 'ica_badsernum_items'
	);

	protected function setup($r)
	{
	}

	public function getIndex($r) 
	{
		$r->renderResponse('hello utility');
	}

	public function getIcaBadsernumItemsJson($r) 
	{
		$items = array();
		$sql = "
			SELECT item.id FROM
			item, attribute, value
			WHERE item.id = value.item_id
			AND value.attribute_id = attribute.id
			AND attribute.ascii_id = 'Serial_Number'
			AND value.value_text != item.serial_number
			";

		$st = Dase_DBO::query($sql);
		while ($item_id = $st->fetchColumn()) {
			$item = new Dase_DBO_Item;
			$item->load($item_id);
			$edit = $item->getBaseUrl(); 
			$edit_media = $item->getEditMediaUrl(); 
			$items[$edit]['serial_number'] = $edit;
			$items[$edit]['edit'] = $edit;
			$items[$edit]['edit-media'] = $edit_media;
			foreach ($item->getMetadata() as $row) {
				$items[$edit][$row['ascii_id']] = $row['value_text'];
			}
		}
		$r->renderResponse(Dase_Json::get($items));
	}
}

