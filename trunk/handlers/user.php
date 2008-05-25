<?php

class UserHandler
{
	//rewrite/replace for alternate authentication
	public static function initiateLogin($request)
	{
		$t = new Dase_Template($request);
		Dase::display($t->fetch('login_form.tpl'),$request);
	}

	//rewrite/replace for alternate authentication
	public static function processLogin($request)
	{
		$username = $request->get('username');
		$pass = $request->get('password');
		if ('tseliot' == $pass) {
			Dase_Cookie::set($username);
			//do this so cookie is passed along
			Dase::redirect("login/$username");
		} else {
			//I could probably just display here instead of redirect
			Dase::redirect("login",'incorrect username/password');
		}
	}

	public static function finishLogin($request)
	{
		//$user = $request->currentUser;

		if ($request->get('eid') == Dase_User::getCurrent()) {
			Dase::redirect('/',"welcome ". $request->get('eid')." is logged in");
		} else {
			Dase::redirect('login');
		}
	}

	public static function logoff($request)
	{
		Dase_User::logoff();
		Dase::redirect('login');
	}

	public static function dataAsJson($request)
	{
		//NOTE WELL!!!:
		//note that we ONLY use the request_url so the IE cache-busting
		//timestamp is ignored.  We can have a long ttl here because ALL
		//operations that change user date are required to expire this cache
		//NOTE: request_url is 'json/user/{eid}/data'
		//need to have SOME data returned if there is no user
		$cache = Dase_Cache::get($request->eid . '_data');
		if (!$cache->isFresh()) {
			$data = Dase_User::get($request->get('eid'))->getData();
			$cache->setData($data);
		}
		header("Content-Type: application/json; charset=utf-8");
		$cache->display();
	}

	public static function cartAsJson($request)
	{
		Dase::display(Dase_User::get($request)->getCart(),$request);
	}

	public static function addCartItem($request)
	{
		$u = Dase_User::get($request);
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

	public static function deleteTagItem($request)
	{
		$u = Dase_User::get($request);
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

	public static function adminCollectionsAsJson($request)
	{
		Dase::display(Dase_User::get($request)->getCollections(),$request);
	}

	public static function cart($request)
	{
		$u = Dase_User::get($request);
		$tag = new Dase_DBO_Tag;
		$tag->dase_user_id = $u->id;
		$tag->tag_type_id = CART;
		$tag->findOne();
		$http_pw = Dase_DBO_Tag::getHttpPassword($tag->ascii_id,$u->eid,'read');
		$t = new Dase_Template($request);
		$t->assign('items',Dase_Atom_Feed::retrieve(APP_ROOT.'/atom/user/'.$u->eid.'/tag/'.$tag->ascii_id,$u->eid,$http_pw));
		Dase::display($t->fetch('item_set/tag.tpl'),$request);
	}

	public static function settings($request)
	{
		$t = new Dase_Template($request);
		$t->assign('user',Dase_User::get($request));
		Dase::display($t->fetch('user/settings.tpl'),$request);
	}

	public static function getHttpPassword($request) 
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

