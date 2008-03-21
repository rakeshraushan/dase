<?php

class UserHandler
{
	//rewrite/replace for alternate authentication
	public static function initiateLogin($params)
	{
		$msg = Dase_Filter::filterGet('msg');
		$t = new Dase_Xslt;
		$t->stylesheet = XSLT_PATH.'login_form.xsl';
		if ($msg) {
			$t->set('msg',$msg);
		}
		Dase::display($t->transform());
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
		$page = $cache->getData();
		if (!$page) {
			$cache->setTimeToLive(300);
			$page = Dase_User::get($params['eid'])->getData();
			$cache->setData($page);
		}
		//passing false as second param 
		//means cache will NOT be reset
		Dase::display($page,false);
	}

	public static function cartAsJson($params)
	{
		Dase::display(Dase_User::get($params)->getCart());
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
			Dase_Error::report(401);
		}
	}

	public static function adminCollectionsAsJson($params)
	{
		Dase::display(Dase_User::get($params)->getCollections());
	}

	public static function cart($params)
	{
		$u = Dase_User::get($params);
		$tag = new Dase_DBO_Tag;
		$tag->dase_user_id = $u->id;
		$tag->tag_type_id = CART;
		$tag->findOne();
		$t = new Dase_Xslt;
		$t->stylesheet = XSLT_PATH.'item_set/tag.xsl';

		//THIS script is protected by eid auth, but how to protect restricted
		//atom and xml documents that feed it? DASe requests AND serves the docs
		//so we can hash a secret in the url and read that for the 'token' auth (see Dase.php)
		$t->set('src',APP_ROOT.'/atom/user/'.$u->eid.'/tag/id/'.$tag->id.'?token='.md5(Dase_Config::get('token')));
		Dase::display($t->transform());
	}

	public static function tag($params)
	{
		//this probably belongs in the tag handler!
		$u = Dase_User::get($params);
		$tag = new Dase_DBO_Tag;
		if (isset($params['id'])) {
			$tag->load($params['id']);
			if ($tag->dase_user_id != $u->id) {
				Dase_Error::report(401);
			}
		} elseif (isset($params['ascii_id'])) {
			$tag->ascii_id = $params['ascii_id'];
			$tag->dase_user_id = $u->id;
			if (!$tag->findOne()) {
				Dase_Error::report(401);
			}
		} else {
			Dase_Error::report(404);
		}

		$t = new Dase_Xslt;
		$t->stylesheet = XSLT_PATH.'item_set/tag.xsl';
		//THIS script is protected by eid auth, but how to protect restricted
		//atom and xml documents that feed it? DASe requests AND serves the docs
		//so we can hash a secret in the url and read that for the 'token' auth (see Dase.php)
		$t->set('src',APP_ROOT.'/atom/user/'.$u->eid.'/tag/id/'.$tag->id.'?token='.md5(Dase_Config::get('token')));
		//print(APP_ROOT.'/atom/user/'.$u->eid.'/tag/id/'.$tag->id.'?token='.md5(Dase_Config::get('token')));exit;
		Dase::display($t->transform());
	}

	public static function settings($params)
	{
		print "user settings access not implemented";
	}
}

