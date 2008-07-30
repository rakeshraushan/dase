<?php

class Dase_Handler_Tag extends Dase_Handler
{

	public $resource_map = array( 
		'{tag_id}' => 'tag',
		'{tag_id}/slideshow' => 'slideshow',
		'{eid}/{tag_ascii_id}' => 'tag',
		'{eid}/{tag_ascii_id}/template' => 'tag_template',
		'{eid}/{tag_ascii_id}/slideshow' => 'slideshow',
		//for set delete:
		'{eid}/{tag_ascii_id}/items' => 'tag_items',
		'item/{tag_id}/{tag_item_id}' => 'tag_item',
		'{eid}/{tag_ascii_id}/{tag_item_id}' => 'tag_item',
		'{eid}/{tag_ascii_id}/{tag_item_id}/sorter' => 'sorter',
		'{eid}/{tag_ascii_id}/item/{collection_ascii_id}/{serial_number}' => 'tag_item',
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

	public function postToSorter($request)
	{
		$new_order = file_get_contents("php://input");
		if ($new_order < 0) {
			$new_order = 0;
		}
		$tag_item = new Dase_DBO_TagItem;
		$tag_item->load($request->get('tag_item_id'));
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
		$t->assign('http_pw',$u->getHttpPassword());
		$request->renderResponse($t->fetch('item_set/slideshow.tpl'));

	}

	public function getTagJson($request)
	{
		$u = $request->getUser();
		if (!$u->can('read','tag',$this->tag)) {
			$request->renderError(401);
		}
		$request->renderResponse($this->tag->asJson());
	}

	public function getTagTemplate($request)
	{
		$t = new Dase_Template($request);
		$request->renderResponse($t->fetch('item_set/jstemplates.tpl'));
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
		if (!$u->can('write','tag',$tag)) {
			$request->renderError(401);
		}
		$item_uniques_array = explode(',',$request->get('item_uniques'));
		$num = count($item_uniques_array);
		foreach ($item_uniques_array as $item_unique) {
			$tag->addItem($item_unique);
		}
		$request->response_mime_type = 'text/plain';
		$request->renderResponse("added $num items to $tag->name");
	}

	public function deleteTagItems($request) 
	{
		$tag = $this->tag;
		$u = $request->getUser();
		$u->expireDataCache();
		if (!$u->can('write','tag',$tag)) {
			$request->renderError(401,'user does not have write privileges');
		}
		$item_uniques_array = explode(',',$request->get('uniques'));
		$num = count($item_uniques_array);
		foreach ($item_uniques_array as $item_unique) {
			$tag->removeItem($item_unique);
		}
		$tag->resortTagItems();
		$request->response_mime_type = 'text/plain';
		$request->renderResponse("removed $num items from $tag->name");
	}
}

