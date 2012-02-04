<?php

class Dase_Handler_Item extends Dase_Handler
{
		public $resource_map = array(
				'list' => 'list',
				'{id}' => 'item',
				'{id}/edit' => 'edit_form',
		);

		protected function setup($r)
		{
				$this->user = $r->getUser();
		}

		public function getListJson($r) 
		{
				$items = new Dase_DBO_Item($this->db);
				$items->orderBy('updated DESC');
				$set = array();
				foreach ($items->find() as $item) {
						$item = clone($item);
						$set[] = $item->asArray();
				}
				$r->renderResponse(Dase_Json::get($set));
		}

		public function getItemJson($r) 
		{
				$item = new Dase_DBO_Item($this->db);
				$item->load($r->get('id'));
				$r->renderResponse($item->asJson($r));
		}
}

