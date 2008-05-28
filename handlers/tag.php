<?php

class TagHandler
{
	public static function asAtom($request)
	{
		$u = Dase_User::get($request);
		$tag = new Dase_DBO_Tag;
		if ($request->has('id')) {
			$tag->load($request->get('id'));
			if ($tag->dase_user_id != $u->id) {
				$request->renderError(401);
			}
		} elseif ($request->has('tag_ascii_id')) {
			$tag->ascii_id = $request->get('tag_ascii_id');
			$tag->dase_user_id = $u->id;
			if (!$tag->findOne()) {
				$request->renderError(401);
			}
		} else {
			$request->renderError(404);
		}
		$request->renderResponse($tag->asAtom(),'application/atom+xml');
	}

	public static function get($request)
	{
		$u = Dase_User::get($request);
		$tag = new Dase_DBO_Tag;
		if ($request->has('id')) {
			$tag->load($request->get('id'));
			if ($tag->dase_user_id != $u->id) {
				$request->renderError(401);
			}
		} elseif ($request->has('tag_ascii_id')) {
			$tag->ascii_id = $request->get('tag_ascii_id');
			$tag->dase_user_id = $u->id;
			if (!$tag->findOne()) {
				$request->renderError(401);
			}
		} else {
			$request->renderError(404);
		}

		$http_pw = Dase_DBO_Tag::getHttpPassword($tag->ascii_id,$u->eid,'read');

		$t = new Dase_Template($request);
		$t->assign('items',Dase_Atom_Feed::retrieve(APP_ROOT.'/atom/user/'.$u->eid.'/tag/'.$tag->ascii_id,$u->eid,$http_pw));
		$request->renderResponse($t->fetch('item_set/tag.tpl'));
	}

	public static function itemAsAtom($request)
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

	public static function item($request)
	{
		$u = Dase_User::get($request);
		$tag_ascii_id = $request->get('tag_ascii_id');
		$tag_item_id = $request->get('tag_item_id');
		$http_pw = Dase_DBO_Tag::getHttpPassword($tag_ascii_id,$u->eid,'read');
		$t = new Dase_Template($request);
		$t->assign('item',Dase_Atom_Feed::retrieve(APP_ROOT.'/atom/user/'.$u->eid.'/tag/'.$tag_ascii_id.'/'.$tag_item_id,$u->eid,$http_pw));
		$request->renderResponse($t->fetch('item/transform.tpl'));
	}

	public static function saveToTag($request) 
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

	public static function removeItems($request) 
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

