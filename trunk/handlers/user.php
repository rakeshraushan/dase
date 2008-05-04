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
			Dase::error(401);
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
		$t->addSourceNode($u->asSimpleXml());

		//THIS script is protected by eid auth, but how to protect restricted
		//atom and xml documents that feed it? DASe requests AND serves the docs
		//so we can hash a secret in the url and read that for the 'token' auth (see Dase.php)
		$t->set('src',APP_ROOT.'/atom/user/'.$u->eid.'/tag/id/'.$tag->id.'?token='.md5(Dase::getConfig('token')));
		Dase::display($t->transform());
	}

	public static function settings($params)
	{
		//use admin look, but NO collection specified!@!!!!!!!
		$t = new Dase_Xslt;
		$t->stylesheet = XSLT_PATH.'user/settings.xsl';
		$user = Dase_User::get($params);
		$t->addSourceNode($user->asSimpleXml(true));
		Dase::display($t->transform());
	}

	public static function getHttpPassword($params) 
	{
		//tag_ascii_ids are not unique
		//but that will not matter since
		//eid is included in hash

		if (Dase_Auth::authorize($params['auth_level'],$params)) {
			$eid = $params['eid'];
			if (isset($params['collection_ascii_id'])) {
				$ascii_id = $params['collection_ascii_id'];
			} elseif (isset($params['tag_ascii_id'])) {
				$ascii_id = $params['tag_ascii_id'];
			} else {
				Dase::error(401);
			}
			$auth_level = $params['auth_level'];
			$password = substr(md5(Dase::getConfig('token').$eid.$ascii_id.$auth_level),0,8);
			header("Content-Type: text/plain; charset=utf-8");
			echo $password;
			exit;
		} else {
			Dase::error(401);
		}
	}
}

