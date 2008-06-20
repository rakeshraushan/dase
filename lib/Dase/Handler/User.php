<?php

class Dase_Handler_User extends Dase_Handler
{
	public $resource_map = array(
		'{eid}/data' => 'data',
		'{eid}/settings' => 'settings',
		'{eid}/cart' => 'cart',
		'{eid}/tag_items/{tag_item_id}' => 'tag_item',
		'{eid}/collection/{collection_ascii_id}/auth/{auth_level}' => 'http_password',
	);

	protected function setup($request)
	{
		if ($request->has('eid')) {
			$this->user = Dase_DBO_DaseUser::get($request->get('eid'));
			$this->user->getHttpPassword();
		}
	}

	public function getDataJson($request)
	{
		//NOTE WELL!!!:
		//note that we ONLY use the request_url so the IE cache-busting
		//timestamp is ignored.  We can have a long ttl here because ALL
		//operations that change user date are required to expire this cache
		//NOTE: request_url is '/user/{eid}/data'
		//need to have SOME data returned if there is no user
		$cache = Dase_Cache::get($request->get('eid') . '_data');
		$data = $cache->getData(3000);
		if (!$data) {
			$data = $request->getUser()->getData();
			$cache->setData($data);
		}
		$request->renderResponse($data);
	}

	public function getCartJson($request)
	{
		$request->renderResponse($this->user->getCart());
	}

	public function postToCart($request)
	{
		$u = $this->user;
		$u->expireDataCache();
		$tag = new Dase_DBO_Tag;
		$tag->dase_user_id = $u->id;
		$tag->tag_type_id = CART;
		$tag->findOne();
		$tag_item = new Dase_DBO_TagItem;
		$tag_item->item_id = $request->get('item_id');
		$tag_item->tag_id = $tag->id;
		if ($tag_item->insert()) {
			$request->renderResponse("added cart item $tag_item->id");
		} else {
			$request->renderResponse("add to cart failed");
		}
	}

	public function deleteTagItem($request)
	{
		$u = $this->user;
		$u->expireDataCache();
		$tag_item = new Dase_DBO_TagItem;
		$tag_item->load($request->get('tag_item_id'));
		$tag = new Dase_DBO_Tag;
		$tag->load($tag_item->tag_id);
		if ($tag->dase_user_id == $u->id) {
			$tag_item->delete();
			$request->renderResponse("tag item ".$request->get('tag_item_id')." deleted!");
			exit;
		} else {
			$request->renderError(401);
		}
	}

	public function adminCollectionsAsJson($request)
	{
		$request->renderResponse(Dase_User::get($request)->getCollections(),$request);
	}

	public function cart($request)
	{
		$u = Dase_User::get($request);
		$tag = new Dase_DBO_Tag;
		$tag->dase_user_id = $u->id;
		$tag->tag_type_id = CART;
		$tag->findOne();
		$http_pw = Dase_DBO_Tag::getHttpPassword($tag->ascii_id,$u->eid,'read');
		$t = new Dase_Template($request);
		$t->assign('items',Dase_Atom_Feed::retrieve(APP_ROOT.'/atom/user/'.$u->eid.'/tag/'.$tag->ascii_id,$u->eid,$http_pw));
		$request->renderResponse($t->fetch('item_set/tag.tpl'),$request);
	}

	public function getSettings($request)
	{
		$t = new Dase_Template($request);
		$t->assign('user',$this->user);
		$request->renderResponse($t->fetch('user/settings.tpl'),$request);
	}

	public function getHttpPassword($request) 
	{
		//this handler required eid authentication
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
				$request->renderError(401);
			}
			header("Content-Type: text/plain; charset=utf-8");
			echo $password;
			exit;
		} else {
			$request->renderError(401);
		}
	}
}

