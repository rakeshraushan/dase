<?php

class TagHandler
{
	public static function asAtom($params)
	{
		$u = Dase_User::get($params);
		$tag = new Dase_DBO_Tag;
		if (isset($params['id'])) {
			$tag->load($params['id']);
			if ($tag->dase_user_id != $u->id) {
				Dase::error(401);
			}
		} elseif (isset($params['ascii_id'])) {
			$tag->ascii_id = $params['ascii_id'];
			$tag->dase_user_id = $u->id;
			if (!$tag->findOne()) {
				Dase::error(401);
			}
		} else {
			Dase::error(404);
		}
		Dase::display($tag->asAtom());
	}

	public static function get($params)
	{
		$u = Dase_User::get($params);
		$tag = new Dase_DBO_Tag;
		if (isset($params['id'])) {
			$tag->load($params['id']);
			if ($tag->dase_user_id != $u->id) {
				Dase::error(401);
			}
		} elseif (isset($params['ascii_id'])) {
			$tag->ascii_id = $params['ascii_id'];
			$tag->dase_user_id = $u->id;
			if (!$tag->findOne()) {
				Dase::error(401);
			}
		} else {
			Dase::error(404);
		}

		$t = new Dase_Template;
		//THIS script is protected by eid auth, but how to protect restricted
		//atom and xml documents that feed it? DASe requests AND serves the docs
		//so we can hash a secret in the url and read that for the 'token' auth (see Dase.php)
		$t->assign('items',Dase_Atom_Feed::retrieve(APP_ROOT.'/atom/user/'.$u->eid.'/tag/id/'.$tag->id.'?token='.md5(Dase::getConfig('token').$u->eid)));
		Dase::display($t->fetch('item_set/tag.tpl'));
	}

	public static function itemAsAtom($params)
	{
		$tag = new Dase_DBO_Tag;
		$tag->ascii_id = $params['ascii_id'];
		if (!$tag->findOne()) {
			Dase::error(401);
		}
		$tag_item = new Dase_DBO_TagItem;
		$tag_item->load($params['tag_item_id']);
		if ($tag_item->tag_id != $tag->id) {
			Dase::error(404);
		} 
		Dase::display($tag_item->asAtom());
	}

	public static function item($params)
	{
		$u = Dase_User::get($params);
		$tag_ascii_id = $params['ascii_id'];
		$tag_item_id = $params['tag_item_id'];

		$t = new Dase_Template;
		//THIS script is protected by eid auth, but how to protect restricted
		//atom and xml documents that feed it? DASe requests AND serves the docs
		//so we can hash a secret in the url and read that for the 'token' auth (see Dase.php)
		$t->assign('item',Dase_Atom_Feed::retrieve(APP_ROOT.'/atom/user/'.$u->eid.'/tag/'.$tag_ascii_id.'/'.$tag_item_id.'?token='.md5(Dase::getConfig('token').$u->eid)));
		Dase::display($t->fetch('item/transform.tpl'));
	}

	public static function saveToTag($params) 
	{
		$item_id_array = explode(',',Dase_Filter::filterPost('item_ids'));
		$u = Dase_User::get($params);
		$tag = new Dase_DBO_Tag;
		$tag->ascii_id = $params['tag_ascii_id'];
		$tag->dase_user_id = $u->id;
		$tag->findOne();
		$num = count($item_id_array);
		foreach ($item_id_array as $item_id) {
			$tag->addItem($item_id);
		}
		echo "added $num items to $tag->name";
		exit;
	}

	public static function removeItems($params) 
	{
		$delete = Dase_Filter::filterPost('delete_tag');
		$item_id_array = Dase_Filter::filterArray($_POST['item_id']);
		$u = Dase_User::get($params);
		$tag = new Dase_DBO_Tag;
		$tag->ascii_id = $params['tag_ascii_id'];
		$tag->dase_user_id = $u->id;
		$tag->findOne();
		if ($delete && !$tag->getItemCount()) {
			//this means we are DELETING tag
			$name = $tag->name;
			$tag->delete();
			Dase::redirect("/","Deleted $name");
		}
		$num = count($item_id_array);
		foreach ($item_id_array as $item_id) {
			$tag->removeItem($item_id);
		}
		Dase::redirect("user/$u->eid/tag/$tag->ascii_id","$num items removed");
	}
}

