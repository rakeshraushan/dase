<?php

class Dase_Handler_Tag extends Dase_Handler
{

	public $resource_map = array( 
		'{tag_id}' => 'tag',
		'{tag_id}/slideshow' => 'slideshow',
		'{eid}/{tag_ascii_id}' => 'tag',
		'{eid}/{tag_ascii_id}/slideshow' => 'slideshow',
		'item/{tag_id}/{tag_item_id}' => 'tag_item',
		'{eid}/{tag_ascii_id}/{tag_item_id}' => 'tag_item',
		'{eid}/{tag_ascii_id}/item/{collection_ascii_id}/{serial_number}' => 'tag_item',
		//for set delete:
		'{eid}/{tag_ascii_id}/items/{item_ids}' => 'tag_items',
	);

	protected function setup($request)
	{
		//Locates requested tag.  Method still needs to authorize.
		$tag = new Dase_DBO_Tag;
		if ($request->has('tag_ascii_id') && $request->has('eid')) {
			$tag->ascii_id = $request->get('tag_ascii_id');
			$tag->eid = $request->get('eid');
			$found = $tag->findOne();
		} elseif ($request->has('tag_id')) {
			$found = $tag->load($request->get('tag_id'));
		} 
		if ($found) {
			$this->tag = $tag;
		} else {
			$request->renderError(404,'no such tag');
		}
	}	

	public function getTagAtom($request)
	{
		/*
		$u = $request->getUser('http');
		if (!$u->can('read','tag',$this->tag)) {
			$request->renderError(401,'user '.$u->eid.' is not authorized to read tag');
		}
		 */
		$request->renderResponse($this->tag->asAtom());
	}

	public function getSlideshow($request)
	{
		$u = $request->getUser();
		$t = new Dase_Template($request);
		//cannot use eid/ascii since it'll sometimes be another user's tag
		$t->assign('json_url',APP_ROOT.'/tag/'.$this->tag->id.'.json');
		$t->assign('eid',$u->eid);
		$t->assign('http_pw',$this->tag->getHttpPassword($u->eid));
		$request->renderResponse($t->fetch('item_set/slideshow.tpl'));

	}

	public function getTagJson($request)
	{
		$u = $request->getUser('http');
		if (!$u->can('read','tag',$this->tag)) {
			$request->renderError(401);
		}
		$request->renderResponse($this->tag->asJson());
	}

	public function getTag($request)
	{
		$u = $request->getUser();
		if (!$u->can('read','tag',$this->tag)) {
			$request->renderError(401,$u->eid .' is not authorized to read this resource');
		}
		$http_pw = $u->getHttpPassword();
		$t = new Dase_Template($request);
		//cannot use eid/ascii since it'll sometimes be another user's tag
		$json_url = APP_ROOT.'/tag/'.$this->tag->id.'.json';
		$t->assign('json_url',$json_url);
		$feed_url = APP_ROOT.'/tag/'.$this->tag->id.'.atom';
		$t->assign('feed_url',$feed_url);
		$t->assign('items',Dase_Atom_Feed::retrieve($feed_url,$u->eid,$http_pw));
		$request->renderResponse($t->fetch('item_set/tag.tpl'));
	}

	public function getTagItemAtom($request)
	{
		$tag_item = new Dase_DBO_TagItem;
		$tag_item->load($request->get('tag_item_id'));
		if ($tag_item->tag_id != $this->tag->id) {
			$request->renderError(404);
		} 
		$request->renderResponse($tag_item->asAtom());
	}

	public function getTagItem($request)
	{
		$u = $request->getUser();
		$tag_ascii_id = $request->get('tag_ascii_id');
		$tag_item_id = $request->get('tag_item_id');
		$http_pw = $u->getHttpPassword();
		$t = new Dase_Template($request);
		//$t->assign('item',Dase_Atom_Feed::retrieve(APP_ROOT.'/tag/'.$u->eid.'/'.$tag_ascii_id.'/'.$tag_item_id.'?format=atom',$u->eid,$http_pw));
		$t->assign('item',Dase_Atom_Feed::retrieve(APP_ROOT.'/tag/item/'.$this->tag->id.'/'.$tag_item_id.'?format=atom',$u->eid,$http_pw));
		$request->renderResponse($t->fetch('item/transform.tpl'));
	}

	public function postToTag($request) 
	{
		$tag = $this->tag;
		$u = $request->getUser();
		$u->expireDataCache();
		if (!$u->can('write',$tag)) {
			$request->renderError(401);
		}
		$item_id_array = explode(',',$request->get('item_ids'));
		$num = count($item_id_array);
		foreach ($item_id_array as $item_id) {
			$tag->addItem($item_id);
		}
		$request->response_mime_type = 'text/plain';
		$request->renderResponse("added $num items to $tag->name");
	}

	public function deleteTagItems($request) 
	{
		$tag = $this->tag;
		$u = $request->getUser();
		$u->expireDataCache();
		if (!$u->can('write',$tag)) {
			$request->renderError(401);
		}
		$item_id_array = explode(',',$request->get('item_ids'));
		$num = count($item_id_array);
		foreach ($item_id_array as $item_id) {
			$tag->removeItem($item_id);
		}
		$request->response_mime_type = 'text/plain';
		$request->renderResponse("removed $num items from $tag->name");
	}
}

