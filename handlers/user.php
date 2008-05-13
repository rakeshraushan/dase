<?php

class UserHandler
{
	//rewrite/replace for alternate authentication
	public static function initiateLogin($params)
	{
		$t = new Dase_Template;
		Dase::display($t->fetch('login_form.tpl'));
	}

	//rewrite/replace for alternate authentication
	public static function processLogin($params)
	{
		$user = Dase_Filter::filterPost('username');
		$pass = Dase_Filter::filterPost('password');
		if ('tseliot' == $pass) {
			Dase_Cookie::set($user);
			//do this so cookie is passed along
			Dase::redirect("login/$user");
		} else {
			//I could probably just display here instead of redirect
			Dase::redirect("login",'incorrect username/password');
		}
	}

	public static function finishLogin($params)
	{
		if (isset($params['eid'])) {
			if ($params['eid'] == Dase_User::getCurrent()) {
				Dase::redirect('/',"welcome {$params['eid']} is logged in");
			} else {
				Dase::redirect('login');
			}
		}
	}

	public static function logoff($params)
	{
		Dase_User::logoff();
		Dase::redirect('login');
	}

	public static function dataAsJson($params)
	{
		//NOTE WELL!!!:
		//note that we ONLY use the request_url so the IE cache-busting
		//timestamp is ignored.  We can have a long ttl here because ALL
		//operations that change user date are required to expire this cache
		//NOTE: request_url is 'json/user/{eid}/data'
		//need to have SOME data returned if there is no user
		if (!isset($params['eid'])) {
			echo "user data error"; exit;
		}
		$cache = Dase_Cache::get($params['eid'] . '_data');
		if (!$cache->isFresh()) {
			$data = Dase_User::get($params['eid'])->getData();
			$headers = array("Content-Type: application/json; charset=utf-8");
			$cache->setData($data,$headers);
		}
		$cache->display();
	}

	public static function cartAsJson($params)
	{
		Dase::display(Dase_User::get($params)->getCart(),'application/json');
	}

	public static function addCartItem($params)
	{
		$u = Dase_User::get($params);
		$u->expireDataCache();
		$tag = new Dase_DBO_Tag;
		$tag->dase_user_id = $u->id;
		$tag->tag_type_id = CART;
		$tag->findOne();
		$tag_item = new Dase_DBO_TagItem;
		$tag_item->item_id = Dase_Filter::filterPost('item_id');
		$tag_item->tag_id = $tag->id;
		if ($tag_item->insert()) {
			echo "added cart item $tag_item->id";
		} else {
			echo "add to cart failed";
		}
	}

	public static function deleteTagItem($params)
	{
		$u = Dase_User::get($params);
		$u->expireDataCache();
		$tag_item = new Dase_DBO_TagItem;
		$tag_item->load($params['tag_item_id']);
		$tag = new Dase_DBO_Tag;
		$tag->load($tag_item->tag_id);
		if ($tag->dase_user_id == $u->id) {
			$tag_item->delete();
			echo "tag item {$params['tag_item_id']} deleted!";
			exit;
		} else {
			Dase::error(401);
		}
	}

	public static function adminCollectionsAsJson($params)
	{
		Dase::display(Dase_User::get($params)->getCollections(),'application/json');
	}

	public static function cart($params)
	{
		$u = Dase_User::get($params);
		$tag = new Dase_DBO_Tag;
		$tag->dase_user_id = $u->id;
		$tag->tag_type_id = CART;
		$tag->findOne();
		$http_pw = Dase_DBO_Tag::getHttpPassword($tag->ascii_id,$u->eid,'read');
		$t = new Dase_Template;
		$t->assign('items',Dase_Atom_Feed::retrieve(APP_ROOT.'/atom/user/'.$u->eid.'/tag/'.$tag->ascii_id,$u->eid,$http_pw));
		Dase::display($t->fetch('item_set/tag.tpl'));
	}

	public static function settings($params)
	{
		$t = new Dase_Template;
		$t->assign('user',Dase_User::get($params));
		Dase::display($t->fetch('user/settings.tpl'));
	}

	public static function getHttpPassword($params) 
	{
		//this handler required eid authentication,
		//meaning the url eid matches the cookie eid

		//first, is *this* user authorized to do what
		//they are asking for an http password to do.
		if (Dase_Auth::authorize($params['auth_level'],$params)) {
			//If so, generate password
			$password = '';
			if (isset($params['collection_ascii_id'])) {
				$password = Dase_DBO_Collection::getHttpPassword($params['collection_ascii_id'],$params['eid'],$params['auth_level']);
			} elseif (isset($params['tag_ascii_id'])) {
				$password = Dase_DBO_Tag::getHttpPassword($params['tag_ascii_id'],$params['eid'],$params['auth_level']);
			} else {
				Dase::error(401);
			}
			header("Content-Type: text/plain; charset=utf-8");
			echo $password;
			exit;
		} else {
			Dase::error(401);
		}
	}
}

