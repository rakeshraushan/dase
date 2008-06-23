<?php

class Dase_Handler_User extends Dase_Handler
{
	public $resource_map = array(
		'{eid}/data' => 'data',
		'{eid}/settings' => 'settings',
		'{eid}/cart' => 'cart',
		'{eid}/auth' => 'http_password',
		'{eid}/tag_items/{tag_item_id}' => 'tag_item',
	);

	protected function setup($request)
	{
		$this->user = $request->getUser();
		if ($request->get('eid') != $this->user->eid) {
			$request->renderError(401,'One must be so careful these days.');
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
		$request->renderResponse($this->user->getCartJson());
	}

	public function postToCart($request)
	{
		$u = $this->user;
		$u->expireDataCache();
		$tag = new Dase_DBO_Tag;
		$tag->dase_user_id = $u->id;
		$tag->type = 'cart';
		if ($tag->findOne()) {
			$tag_item = new Dase_DBO_TagItem;
			$tag_item->item_id = $request->get('item_id');
			$tag_item->tag_id = $tag->id;
			$tag_item->updated = date(DATE_ATOM);
			if ($tag_item->insert()) {
				//writes are expensive ;-)
				$tag_item->persist();
				$request->renderResponse("added cart item $tag_item->id");
			} else {
				$request->renderResponse("add to cart failed");
			}
		} else {
			$request->renderResponse("no such cart");
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

	public function getCart($request)
	{
		$u = $this->user;
		$tag = new Dase_DBO_Tag;
		$tag->dase_user_id = $u->id;
		$tag->type = 'cart';
		if ($tag->findOne()) {
			$http_pw = $u->getHttpPassword();
			$t = new Dase_Template($request);
			$t->assign('items',Dase_Atom_Feed::retrieve(APP_ROOT.'/tag/'.$tag->id.'.atom',$u->eid,$http_pw));
			$request->renderResponse($t->fetch('item_set/tag.tpl'));
		} else {
			$request->renderError(404);
		}
	}

	public function getSettings($request)
	{
		$t = new Dase_Template($request);
		$t->assign('user',$this->user);
		$t->assign('http_password',$this->user->getHttpPassword());
		$request->renderResponse($t->fetch('user/settings.tpl'),$request);
	}

	public function getHttpPassword($request) 
	{
		$u = $this->user;
		$request->renderResponse($u->getHttpPassword());
	}
}

