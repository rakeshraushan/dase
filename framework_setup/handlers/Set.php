<?php

class Dase_Handler_Set extends Dase_Handler
{
		public $resource_map = array(
				'list' => 'list', //list all sets
				'form' => 'form', //create a set
				'{name}' => 'set', //add/delete items (html) or view (json)
				'{id}/edit' => 'edit_form', 
				'{id}/remove' => 'remove', 
		);

		protected function setup($r)
		{
				$this->user = $r->getUser();
		}

		public function getForm($r) 
		{
				$t = new Dase_Template($r);
				$r->renderResponse($t->fetch('set_form.tpl'));
		}

		public function postToEditForm($r) 
		{
				$set = new Dase_DBO_Itemset($this->db);
				if (!$set->load($r->get('id'))) {
						$r->renderError(404);
				}
				$set->title = $r->get('title');
				if (!$set->title) {
						$set->title = dechex(time());
				}
				$set->name = $this->_findUniqueName(Dase_Util::dirify($set->title));
				$set->update();
				$r->renderRedirect('set/'.$set->name);
		}

		public function getEditForm($r) 
		{
				$t = new Dase_Template($r);
				$set = new Dase_DBO_Itemset($this->db);
				if (!$set->load($r->get('id'))) {
						$r->renderRedirect('set/list');
						//$r->renderError(404);
				}
				$t->assign('set',$set);
				$r->renderResponse($t->fetch('set_edit.tpl'));
		}

		public function deleteSet($r)
		{
				$set = new Dase_DBO_Itemset($this->db);
				if (!$set->load($r->get('name'))) {
						$r->renderError(404);
				}
				if (!$this->user->is_admin) {
						$r->renderError(401);
				}

				$set->removeItems();
				$set->delete();
				$r->renderResponse('deleted set');
		}
		public function postToRemove($r)
		{
				$set = new Dase_DBO_Itemset($this->db);
				if ($set->load($r->get('id'))) {
						$is_item = new Dase_DBO_ItemsetItem($this->db);
						$is_item->itemset_id = $set->id;
						$is_item->item_id = $r->get('item_id');
						if ($is_item->findOne()) {
								$is_item->delete();
						}
				}
				$r->renderRedirect('set/'.$set->name);
		}

		public function getList($r) 
		{
				$t = new Dase_Template($r);
				$sets = new Dase_DBO_Itemset($this->db);
				$sets->orderBy('created DESC');
				$t->assign('sets',$sets->findAll(1));
				$r->renderResponse($t->fetch('sets.tpl'));
		}

		public function getSet($r) 
		{
				$t = new Dase_Template($r);
				$set = new Dase_DBO_Itemset($this->db);
				$set->name = $r->get('name');
				if ($set->findOne()) {
						$item_ids_array = $set->getItemIds();
						$t->assign('set',$set);
						$items = new Dase_DBO_Item($this->db);
						$items->orderBy('updated DESC');
						$has_items = array();
						$not_items = array();
						foreach ($items->find() as $item) {
								$item = clone($item);
								if (in_array($item->id,$item_ids_array)) {
										$has_items[] = $item;
								} else {
										$not_items[] = $item;
								}
						}	
						$sorted_has = array();
						foreach ($item_ids_array as $has_id) {
								foreach ($has_items as $has) {
										if ($has->id == $has_id) {
												$sorted_has[] = $has;
										}
								}
						}
						$t->assign('not_items',$not_items);
						$t->assign('has_items',$sorted_has);
						$r->renderResponse($t->fetch('set.tpl'));
				} else {
						$r->renderError(404);
				}
		}

		public function postToSet($r) 
		{
				$set = new Dase_DBO_Itemset($this->db);
				if ($set->load($r->get('name'))) {
						$is_item = new Dase_DBO_ItemsetItem($this->db);
						$is_item->itemset_id = $set->id;
						$is_item->item_id = $r->get('item_id');
						$is_item->created = date(DATE_ATOM);
						$is_item->insert();
				}
				$r->renderRedirect('set/'.$set->name);
		}

		public function getSetJson($r) 
		{
				$set = new Dase_DBO_Itemset($this->db);
				$set->name = $r->get('name');
				if ( $set->findOne()) {
						$r->renderResponse($set->asJson($r));
				} else {
						$r->renderError(404);
				}
		}

		public function postToForm($r)
		{
				$set = new Dase_DBO_Itemset($this->db);
				$set->title = $r->get('title');
				if (!$set->title) {
						$set->title = dechex(time());
				}
				$set->name = $this->_findUniqueName(Dase_Util::dirify($set->title));
				$set->created_by = $this->user->eid;
				$set->created = date(DATE_ATOM);
				$set->insert();
				$r->renderRedirect('set/'.$set->name);
		}

		private function _findUniqueName($name,$iter=0)
		{
				if ($iter) {
						$checkname = $name.'_'.$iter;
				} else {
						$checkname = $name;
				}
				$set = new Dase_DBO_Itemset($this->db);
				$set->name = $checkname;
				if (!$set->findOne()) {
						return $checkname;
				} else {
						$iter++;
						return $this->_findUniqueName($name,$iter);
				}
		}
}

