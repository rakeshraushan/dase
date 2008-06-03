<?php

class TagHandler extends Dase_Handler
{

	public $resource_map = array( 
		'{tag_id}' => 'tag',
		'{eid}/{tag_ascii_id}' => 'tag',
		'{eid}/{tag_ascii_id}/{tag_item_id}' => 'tag_item',
		'{eid}/{tag_ascii_id}/items/{item_ids}' => 'tag_items',
	);

	protected function setup($request)
	{
		if ($request->has('tag_ascii_id') && $request->has('eid')) {
			$this->tag = Dase_DBO_Tag::get($request->get('tag_ascii_id'),$request->get('eid'));
		} elseif ($request->has('tag_id')) {
			$this->tag = new Dase_DBO_Tag;
			$this->tag->load($request->get('tag_id'));
		}
		//todo: authorize access to tag!!!!!
	}	

	public function getTagAtom($request)
	{
		$u = $request->getHttpUser($this->tag);
		if (!$u->can('read',$this->tag)) {
			$request->renderError(401);
		}
		$request->renderResponse($this->tag->asAtom());
	}

	public function getTag($request)
	{
		$u = $request->getUser();
		if (!$u->can('read',$this->tag)) {
			$request->renderError(401);
		}
		$http_pw = $this->tag->getHttpPassword($u->eid);
		$t = new Dase_Template($request);
		//cannot use eid/ascii since it'll sometimes be anotehr user's tag
		$t->assign('items',Dase_Atom_Feed::retrieve(APP_ROOT.'/tag/'.$this->tag->id.'.atom',$u->eid,$http_pw));
		$request->renderResponse($t->fetch('item_set/tag.tpl'));
	}

	public function getTagItemAtom($request)
	{
		$tag = new Dase_DBO_Tag;
		$tag->ascii_id = $request->get('tag_ascii_id');
		if (!$tag->findOne()) {
			$request->renderError(401);
		}
		$tag_item = new Dase_DBO_TagItem;
		$tag_item->load($request->get('tag_item_id'));
		if ($tag_item->tag_id != $tag->id) {
			$request->renderError(404);
		} 
		$request->renderResponse($tag_item->asAtom());
	}

	public function getTagItem($request)
	{
		$u = $request->getUser();
		$tag_ascii_id = $request->get('tag_ascii_id');
		$tag_item_id = $request->get('tag_item_id');
		$http_pw = $this->tag->getHttpPassword($u->eid);
		$t = new Dase_Template($request);
		$t->assign('item',Dase_Atom_Feed::retrieve(APP_ROOT.'/tag/'.$u->eid.'/'.$tag_ascii_id.'/'.$tag_item_id.'?format=atom',$u->eid,$http_pw));
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

