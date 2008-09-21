<?php

class Dase_Handler_Tag extends Dase_Handler
{

	public $resource_map = array( 
		'{tag_id}' => 'tag',
		'{eid}/{tag_ascii_id}' => 'tag',
		'{eid}/{tag_ascii_id}/item_uniques' => 'item_uniques',
		'{eid}/{tag_ascii_id}/template' => 'tag_template',
		'{eid}/{tag_ascii_id}/sorter' => 'tag_sorter',
		//for set delete:
		'{eid}/{tag_ascii_id}/items' => 'tag_items',
		'item/{tag_id}/{tag_item_id}' => 'tag_item',
		'{eid}/{tag_ascii_id}/{tag_item_id}' => 'tag_item',
		'{eid}/{tag_ascii_id}/item/{collection_ascii_id}/{serial_number}' => 'tag_item',
	);

	protected function setup($r)
	{
		//Locates requested tag.  Method still needs to authorize.
		$tag = new Dase_DBO_Tag;
		if ($r->has('tag_ascii_id') && $r->has('eid')) {
			$tag->ascii_id = $r->get('tag_ascii_id');
			$tag->eid = $r->get('eid');
			$found = $tag->findOne();
		} elseif ($r->has('tag_id')) {
			$found = $tag->load($r->get('tag_id'));
		} 
		if ($found->id) {
			$this->tag = $tag;
		} else {
			$r->renderError(404,'no such tag');
		}
	}	

	public function postToSorter($r)
	{
		$new_order = file_get_contents("php://input");
		if ($new_order < 0) {
			$new_order = 0;
		}
		$tag_item = new Dase_DBO_TagItem;
		$tag_item->load($r->get('tag_item_id'));
		$old_order = $tag_item->sort_order;
		$tag_item->sort_order = $new_order;
		$tag_item->updated = date(DATE_ATOM);
		$tag_item->update();
		if ($old_order > $new_order) {
			$dir = 'DESC';
		} else {
			$dir = 'ASC';
		}
		$this->tag->resortTagItems($dir);
		echo "done";
		exit;
	}

	public function getTagAtom($r)
	{
		/*
		$u = $r->getUser('http');
		if (!$u->can('read',$this->tag)) {
			$r->renderError(401,'user '.$u->eid.' is not authorized to read tag');
		}
		 */
		$r->renderResponse($this->tag->asAtom());
	}

	public function getTagJson($r)
	{
		$u = $r->getUser();
		if (!$u->can('read',$this->tag)) {
			$r->renderError(401);
		}
		$r->renderResponse($this->tag->asJson());
	}

	public function getItemUniquesTxt($r)
	{
		$u = $r->getUser('http');
		if (!$u->can('read',$this->tag)) {
			$r->renderError(401);
		}
		$output = '';
		foreach($this->tag->getItemUniques() as $iu) {
			if ($iu) {
				$output .= '|'.$iu;
			}
		}
		$r->renderResponse($output);
	}

	public function getTagTemplate($r)
	{
		$t = new Dase_Template($r);
		$r->renderResponse($t->fetch('item_set/jstemplates.tpl'));
	}

	public function getTag($r)
	{
		$u = $r->getUser();
		if (!$u->can('read',$this->tag)) {
			$r->renderError(401,$u->eid .' is not authorized to read this resource');
		}
		$http_pw = $u->getHttpPassword();
		$t = new Dase_Template($r);
		//cannot use eid/ascii since it'll sometimes be another user's tag
		$json_url = APP_ROOT.'/tag/'.$this->tag->id.'.json';
		$t->assign('json_url',$json_url);
		$feed_url = APP_ROOT.'/tag/'.$this->tag->id.'.atom';
		$t->assign('feed_url',$feed_url);
		$t->assign('items',Dase_Atom_Feed::retrieve($feed_url,$u->eid,$http_pw));
		$r->renderResponse($t->fetch('item_set/tag.tpl'));
	}

	public function getTagSorter($r)
	{
		$u = $r->getUser();
		if (!$u->can('read',$this->tag)) {
			$r->renderError(401,$u->eid .' is not authorized to read this resource');
		}
		$http_pw = $u->getHttpPassword();
		$t = new Dase_Template($r);
		$feed_url = APP_ROOT.'/tag/'.$this->tag->id.'.atom';
		$t->assign('tag_feed',Dase_Atom_Feed::retrieve($feed_url,$u->eid,$http_pw));
		$r->renderResponse($t->fetch('item_set/tag_sorter.tpl'));
	}

	public function postToTagSorter($r)
	{
		$u = $r->getUser();
		if (!$u->can('read',$this->tag)) {
			$r->renderError(401,$u->eid .' is not authorized to read this resource');
		}
		$sort_array = $r->get('set_sort_item',true);
		$this->tag->sort($sort_array);
		$http_pw = $u->getHttpPassword();
		$t = new Dase_Template($r);
		$feed_url = APP_ROOT.'/tag/'.$this->tag->id.'.atom';
		$t->assign('tag_feed',Dase_Atom_Feed::retrieve($feed_url,$u->eid,$http_pw));
		$r->renderResponse($t->fetch('item_set/tag_sorter.tpl'));
	}

	public function getTagItemAtom($r)
	{
		$tag_item = new Dase_DBO_TagItem;
		$tag_item->load($r->get('tag_item_id'));
		if ($tag_item->tag_id != $this->tag->id) {
			$r->renderError(404);
		} 
		$r->renderResponse($tag_item->asAtom());
	}

	public function getTagItem($r)
	{
		$u = $r->getUser();
		$tag_ascii_id = $r->get('tag_ascii_id');
		$tag_item_id = $r->get('tag_item_id');
		$http_pw = $u->getHttpPassword();
		$t = new Dase_Template($r);
		//$t->assign('item',Dase_Atom_Feed::retrieve(APP_ROOT.'/tag/'.$u->eid.'/'.$tag_ascii_id.'/'.$tag_item_id.'?format=atom',$u->eid,$http_pw));
		$t->assign('item',Dase_Atom_Feed::retrieve(APP_ROOT.'/tag/item/'.$this->tag->id.'/'.$tag_item_id.'?format=atom',$u->eid,$http_pw));
		$r->renderResponse($t->fetch('item/transform.tpl'));
	}

	public function postToTag($r) 
	{
		$tag = $this->tag;
		$u = $r->getUser();
		$u->expireDataCache();
		if (!$u->can('write',$tag)) {
			$r->renderError(401);
		}
		$item_uniques_array = explode(',',$r->get('item_uniques'));
		$num = count($item_uniques_array);
		foreach ($item_uniques_array as $item_unique) {
			$tag->addItem($item_unique);
		}
		$r->response_mime_type = 'text/plain';
		$r->renderResponse("added $num items to $tag->name");
	}

	public function deleteTagItems($r) 
	{
		$tag = $this->tag;
		$u = $r->getUser();
		$u->expireDataCache();
		if (!$u->can('write',$tag)) {
			$r->renderError(401,'user does not have write privileges');
		}
		$item_uniques_array = explode(',',$r->get('uniques'));
		$num = count($item_uniques_array);
		foreach ($item_uniques_array as $item_unique) {
			$tag->removeItem($item_unique);
		}
		$tag->resortTagItems();
		$r->response_mime_type = 'text/plain';
		$r->renderResponse("removed $num items from $tag->name");
	}
}
