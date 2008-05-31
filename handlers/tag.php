<?php

class TagHandler extends Dase_Handler
{

	public $resource_map = array( 
		'{eid}/{tag_ascii_id}' => 'tag',
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
		if (!$u->can('read','tag',$this->tag->ascii_id)) {
			$request->renderError(401);
		}
		$request->renderResponse($this->tag->asAtom());
	}

	public function getTag($request)
	{
		$u = $request->getUser();
		if (!$u->can('read','tag',$this->tag->ascii_id)) {
			$request->renderError(401);
		}
		$http_pw = $this->tag->getHttpPassword($u->eid);
		$t = new Dase_Template($request);
		$t->assign('items',Dase_Atom_Feed::retrieve(APP_ROOT.'/tag/'.$u->eid.'/'.$this->tag->ascii_id.'.atom',$u->eid,$http_pw));
		$request->renderResponse($t->fetch('item_set/tag.tpl'));
	}

	public function itemAsAtom($request)
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
		$request->renderResponse($tag_item->asAtom(),'application/atom+xml');
	}

	public function item($request)
	{
		$u = Dase_User::get($request);
		$tag_ascii_id = $request->get('tag_ascii_id');
		$tag_item_id = $request->get('tag_item_id');
		$http_pw = Dase_DBO_Tag::getHttpPassword($tag_ascii_id,$u->eid,'read');
		$t = new Dase_Template($request);
		$t->assign('item',Dase_Atom_Feed::retrieve(APP_ROOT.'/atom/user/'.$u->eid.'/tag/'.$tag_ascii_id.'/'.$tag_item_id,$u->eid,$http_pw));
		$request->renderResponse($t->fetch('item/transform.tpl'));
	}

	public function saveToTag($request) 
	{
		$item_id_array = explode(',',Dase_Filter::filterPost('item_ids'));
		$u = Dase_User::get($request);
		$tag = new Dase_DBO_Tag;
		$tag->ascii_id = $request->get('tag_ascii_id');
		$tag->dase_user_id = $u->id;
		$tag->findOne();
		$num = count($item_id_array);
		foreach ($item_id_array as $item_id) {
			$tag->addItem($item_id);
		}
		header("Content-type: text/plain");
		echo "added $num items to $tag->name";
		exit;
	}

	public function removeItems($request) 
	{
		$delete = $request->get('delete_tag');
		$item_id_array = $request->get('item_id',true);
		$u = Dase_User::get($request);
		$tag = new Dase_DBO_Tag;
		$tag->ascii_id = $request->get('tag_ascii_id');
		$tag->dase_user_id = $u->id;
		$tag->findOne();
		if ($delete && !$tag->getItemCount()) {
			//this means we are DELETING tag
			$name = $tag->name;
			$tag->delete();
			$request->renderRedirect("/","Deleted $name");
		}
		$num = count($item_id_array);
		foreach ($item_id_array as $item_id) {
			$tag->removeItem($item_id);
		}
		$request->renderRedirect("user/$u->eid/tag/$tag->ascii_id","$num items removed");
	}
}

