<?php

require_once 'Dase/DBO/Autogen/Itemset.php';

class Dase_DBO_Itemset extends Dase_DBO_Autogen_Itemset 
{
		public function getItemIds()
		{
				$set_items = new Dase_DBO_ItemsetItem($this->db);
				$set_items->itemset_id = $this->id;
				$set_items->orderBy('created');
				$item_ids_array = array();
				foreach ($set_items->findAll(1) as $si) {
						$item_ids_array[] = $si->item_id;
				}
				return $item_ids_array;
		}

		public function asJson($r)
		{
				$result =array();
				$result['id'] = $r->app_root.'/set/'.$this->name;
				$result['title'] = $this->title;
				$result['items'] = array();
				foreach ($this->getItemIds() as $item_id) {
						$item = new Dase_DBO_Item($this->db);
						$item->load($item_id);
						$result['items'][$item->name] = $item->asArray($r);
				}
				return Dase_Json::get($result);
		}

}
