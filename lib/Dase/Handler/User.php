<?php

class Dase_Handler_User extends Dase_Handler
{
	public $resource_map = array(
		'{eid}/data' => 'data',
		'{eid}/settings' => 'settings',
		'{eid}/settings/preferred' => 'preferred_collections',
		'{eid}/display' => 'display',
		'{eid}/controls' => 'controls',
		'{eid}/cart' => 'cart',
		'{eid}/cart/emptier' => 'cart_emptier',
		'{eid}/sets' => 'sets',
		'{eid}/auth' => 'http_password',
		'{eid}/key' => 'key',
		'{eid}/tag_items/{tag_item_id}' => 'tag_item',
		'{eid}/{collection_ascii_id}/recent' => 'recent_items',
	);

	protected function setup($r)
	{
		if ('atom' == $r->format) {
			$this->user = $r->getUser('http');
		} else {
			$this->user = $r->getUser();
		}
		if ($r->get('eid') != $this->user->eid ) {
			$r->renderError(401,'One must be so careful these days.');
		}
	}

	public function getSetsAtom($r)
	{
		$r->renderResponse($this->user->getTagsAsAtom());
	}

	public function getRecentItemsAtom($r)
	{
		//implement http authorization!
		$items = new Dase_DBO_Item;
		$items->created_by_eid = $this->user->eid;
		$items->collection_id = Dase_DBO_Collection::get($r->get('collection_ascii_id'))->id;
		$items->orderBy('created DESC');
		if ($r->has('limit')) {
			$limit = $r->get('limit');
		} else {
			$limit = 50;
		}
		$items->setLimit($limit);
		$feed = new Dase_Atom_Feed;
		$feed->setTitle('Recent Uploads by '.$this->user->eid);
		$feed->setId(APP_ROOT.'user/'.$this->user->eid.'/'.$r->get('collection_ascii_id').'/recent');
		$feed->setFeedType('items');
		$feed->setUpdated(date(DATE_ATOM));
		$feed->addAuthor();
		foreach ($items->find() as $item) {
			$item->injectAtomEntryData($feed->addEntry('item'));
		}
		$r->renderResponse($feed->asXml());
	}

	public function getDataJson($r)
	{
		//NOTE WELL!!!:
		//note that we ONLY use the request_url so the IE cache-busting
		//timestamp is ignored.  We can have a long ttl here because ALL
		//operations that change user date are required to expire this cache
		//NOTE: request_url is '/user/{eid}/data'
		//need to have SOME data returned if there is no user
		$cache = Dase_Cache::get($r->get('eid') . '_data');
		$data = $cache->getData(3000);
		if (!$data) {
			$data = $r->getUser()->getDataJson();
			$cache->setData($data);
		}
		$r->renderResponse($data);
	}

	public function getCartJson($r)
	{
		$r->renderResponse($this->user->getCartJson());
	}

	public function postToCart($r)
	{
		$u = $this->user;
		$u->expireDataCache();
		$tag = new Dase_DBO_Tag;
		$tag->dase_user_id = $u->id;
		$tag->type = 'cart';
		if ($tag->findOne()) {
			$tag_item = new Dase_DBO_TagItem;
			$item_uniq = str_replace(APP_ROOT.'/','',$r->get('item_unique'));
			list($coll,$sernum) = explode('/',$item_uniq);

			//todo: compat 
			$item = Dase_DBO_Item::get($coll,$sernum);
			$tag_item->item_id = $item->id;

			$tag_item->p_collection_ascii_id = $coll;
			$tag_item->p_serial_number = $sernum;;
			$tag_item->tag_id = $tag->id;
			$tag_item->updated = date(DATE_ATOM);
			$tag_item->sort_order = 99999;
			if ($tag_item->insert()) {
				//will not need this when we use item_unique:
				//writes are expensive ;-)
				//$tag_item->persist();
				$tag->updateItemCount();
				$r->renderResponse("added cart item $tag_item->id");
			} else {
				$r->renderResponse("add to cart failed");
			}
		} else {
			$r->renderResponse("no such cart");
		}
	}

	public function postToCartEmptier($r)
	{
		$u = $this->user;
		$u->expireDataCache();
		$tag = new Dase_DBO_Tag;
		$tag->dase_user_id = $u->id;
		$tag->type = 'cart';
		if ($tag->findOne()) {
			foreach ($tag->getTagItems() as $ti) {
				$ti->delete();
			}
			$tag->updateItemcount();
			$params['msg'] = "Your cart has been emptied.";
			$r->renderRedirect("user/$u->eid/cart",$params);
		} else {
			$r->renderResponse("no such cart");
		}
	}

	public function deleteTagItem($r)
	{
		$u = $this->user;
		$u->expireDataCache();
		$tag_item = new Dase_DBO_TagItem;
		$tag_item->load($r->get('tag_item_id'));
		$tag = new Dase_DBO_Tag;
		$tag->load($tag_item->tag_id);
		//todo: make this tag->eid == $u->eid
		if ($tag->dase_user_id == $u->id) {
			$tag_item->delete();
			$tag->updateItemCount();
			$r->renderResponse("tag item ".$r->get('tag_item_id')." deleted!",false);
		} else {
			$r->renderError(401,'user does not own tag');
		}
	}

	public function adminCollectionsAsJson($r)
	{
		$r->renderResponse(Dase_User::get($r)->getCollections(),$r);
	}

	public function getCart($r)
	{
		$u = $this->user;
		$tag = new Dase_DBO_Tag;
		$tag->dase_user_id = $u->id;
		$tag->type = 'cart';
		if ($tag->findOne()) {
			$http_pw = $u->getHttpPassword();
			$t = new Dase_Template($r);
			$json_url = APP_ROOT.'/tag/'.$tag->id.'.json';
			$t->assign('json_url',$json_url);
			$t->assign('items',Dase_Atom_Feed::retrieve(APP_ROOT.'/tag/'.$tag->id.'.atom',$u->eid,$http_pw));
			$t->assign('is_admin',1);
			$r->renderResponse($t->fetch('item_set/tag.tpl'));
		} else {
			$r->renderError(404);
		}
	}

	public function getSettings($r)
	{
		$u= $this->user;
		$t = new Dase_Template($r);
		$u->collections = $u->getCollections();
		$t->assign('user',$u);
		$t->assign('http_password',$u->getHttpPassword());
		$r->renderResponse($t->fetch('user/settings.tpl'),$r);
	}

	public function postToPreferredCollections($r)
	{
		$u = $this->user;
		//filter this!!!
		$preferred_colls = file_get_contents("php://input");
		$u->current_collections = $preferred_colls;
		//see if this messes up access exception setting (bool)
		//try/catch??
		if (!$u->has_access_exception) {
			$u->has_access_exception = 0;
		}
		$u->update();
		$u->expireDataCache();
		$r->renderResponse('ok');
	}

	public function getHttpPassword($r) 
	{
		$u = $this->user;
		$r->renderResponse($u->getHttpPassword());
	}

	public function postToKey($r)
	{
		$u = $this->user;
		$params = array();
		if (strlen($r->get('key')) > 5 ) {
			$u->service_key_md5 = md5($r->get('key'));
			if (!$u->has_access_exception) {
				$u->has_access_exception = 0;
			}
			$u->update();
			$params['msg'] = "your service key has been saved";
		} else {
			$params['msg'] = "your service key must be at least 6 characters";
		}
		$r->renderRedirect("user/$u->eid/settings",$params);
	}

	public function postToDisplay($r)
	{
		Dase_Cookie::set('max',$r->get('max'));
		Dase_Cookie::set('display',$r->get('display'));

		$u = $this->user;
		$u->max_items = $r->get('max');
		$u->display = $r->get('display');
		if (!$u->has_access_exception) {
			$u->has_access_exception = 0;
		}
		$u->update();
		$u->expireDataCache();
		$r->renderRedirect("user/$u->eid/settings");
	}

	public function postToControls($r)
	{
		$u = $this->user;
		//we will use the 'cb' column for controls show/hide for compat
		if ($r->has('controls')) {
			$u->cb = $r->get('controls');
			if (!$u->has_access_exception) {
				$u->has_access_exception = 0;
			}
			$u->update();
			$u->expireDataCache();
		}
		$r->renderRedirect("user/$u->eid/settings");
	}
}

