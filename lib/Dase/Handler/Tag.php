<?php

class Dase_Handler_Tag extends Dase_Handler
{

	public $resource_map = array( 
		'{tag_id}' => 'tag',
		'{eid}/{tag_ascii_id}' => 'tag',
		'{eid}/{tag_ascii_id}/edit' => 'edit',
		'{eid}/{tag_ascii_id}/templates' => 'bulk_edit_templates',
		'{eid}/{tag_ascii_id}/metadata' => 'metadata',
		'{eid}/{tag_ascii_id}/list' => 'tag_list',
		'{eid}/{tag_ascii_id}/grid' => 'tag_grid',
		'{eid}/{tag_ascii_id}/item_uniques' => 'item_uniques',
		'{eid}/{tag_ascii_id}/template' => 'tag_template',
		'{eid}/{tag_ascii_id}/sorter' => 'tag_sorter',
		'{eid}/{tag_ascii_id}/expunger' => 'tag_expunger',
		//for set delete:
		'{eid}/{tag_ascii_id}/items' => 'tag_items',
		'item/{tag_id}/{tag_item_id}' => 'tag_item',
		'{eid}/{tag_ascii_id}/{tag_item_id}' => 'tag_item',
		'{eid}/{tag_ascii_id}/item/{collection_ascii_id}/{serial_number}' => 'tag_item',
	);

	protected function setup($r)
	{
		//Locates requested tag.  Method still needs to authorize.
		$tag = new Dase_DBO_Tag;
		if ($r->has('tag_ascii_id') && $r->has('eid')) {
			$tag->ascii_id = $r->get('tag_ascii_id');
			$tag->eid = $r->get('eid');
			$found = $tag->findOne();
		} elseif ($r->has('tag_id')) {
			$found = $tag->load($r->get('tag_id'));
		} 
		if (isset($found) && $found->id) {
			$this->tag = $tag;
		} else {
			$r->renderError(404,'no such tag');
		}
	}	

	public function getBulkEditTemplates($r)
	{
		$t = new Dase_Template($r);
		$r->renderResponse($t->fetch('item_set/jstemplates.tpl'));
	}

	public function postToSorter($r)
	{
		$new_order = file_get_contents("php://input");
		if ($new_order < 0) {
			$new_order = 0;
		}
		$tag_item = new Dase_DBO_TagItem;
		$tag_item->load($r->get('tag_item_id'));
		$old_order = $tag_item->sort_order;
		$tag_item->sort_order = $new_order;
		$tag_item->updated = date(DATE_ATOM);
		$tag_item->update();
		if ($old_order > $new_order) {
			$dir = 'DESC';
		} else {
			$dir = 'ASC';
		}
		$this->tag->resortTagItems($dir);
		echo "done";
		exit;
	}

	public function getTagAtom($r)
	{
		/*
		$u = $r->getUser('http');
		if (!$u->can('read',$this->tag)) {
			$r->renderError(401,'user '.$u->eid.' is not authorized to read tag');
		}
		 */
		$r->renderResponse($this->tag->asAtom());
	}

	public function getTagJson($r)
	{
		$u = $r->getUser();
		if (!$u->can('read',$this->tag)) {
			$r->renderError(401);
		}
		$r->renderResponse($this->tag->asJson());
	}

	public function getItemUniquesTxt($r)
	{
		$u = $r->getUser('http');
		if (!$u->can('read',$this->tag)) {
			$r->renderError(401);
		}
		$output = '';
		foreach($this->tag->getItemUniques() as $iu) {
			if ($iu) {
				$output .= '|'.$iu;
			}
		}
		$r->renderResponse($output);
	}

	public function getTagTemplate($r)
	{
		$t = new Dase_Template($r);
		$r->renderResponse($t->fetch('item_set/jstemplates.tpl'));
	}

	public function getTagList($r)
	{
		$this->getTag($r,'list');
	}

	public function getTagGrid($r)
	{
		$this->getTag($r,'grid');
	}

	public function getTag($r,$display='')
	{
		$u = $r->getUser();
		if (!$u->can('read',$this->tag)) {
			$r->renderError(401,$u->eid .' is not authorized to read this resource.');
		}
		$http_pw = $u->getHttpPassword();
		$t = new Dase_Template($r);
		//cannot use eid/ascii since it'll sometimes be another user's tag
		$json_url = APP_ROOT.'/tag/'.$this->tag->id.'.json';
		$t->assign('json_url',$json_url);
		$feed_url = APP_ROOT.'/tag/'.$this->tag->id.'.atom';
		$t->assign('feed_url',$feed_url);
		$t->assign('items',Dase_Atom_Feed::retrieve($feed_url,$u->eid,$http_pw));
		if ($u->can('admin',$this->tag) && 'hide' != $u->cb) {
			$t->assign('bulkedit',1);
		}
		if ($u->can('write',$this->tag)) {
			$t->assign('is_admin',1);
		}
		$t->assign('display',$display);
		$r->renderResponse($t->fetch('item_set/tag.tpl'));
	}

	public function getTagSorter($r)
	{
		$u = $r->getUser();
		if (!$u->can('read',$this->tag)) {
			$r->renderError(401,$u->eid .' is not authorized to read this resource');
		}
		$http_pw = $u->getHttpPassword();
		$t = new Dase_Template($r);
		$feed_url = APP_ROOT.'/tag/'.$this->tag->id.'.atom';
		$t->assign('tag_feed',Dase_Atom_Feed::retrieve($feed_url,$u->eid,$http_pw));
		$r->renderResponse($t->fetch('item_set/tag_sorter.tpl'));
	}

	public function postToTagSorter($r)
	{
		$u = $r->getUser();
		if (!$u->can('write',$this->tag)) {
			$r->renderError(401,$u->eid .' is not authorized to write this resource');
		}
		$sort_array = $r->get('set_sort_item',true);
		$this->tag->sort($sort_array);
		$http_pw = $u->getHttpPassword();
		$t = new Dase_Template($r);
		$feed_url = APP_ROOT.'/tag/'.$this->tag->id.'.atom';
		$t->assign('tag_feed',Dase_Atom_Feed::retrieve($feed_url,$u->eid,$http_pw));
		$r->renderResponse($t->fetch('item_set/tag_sorter.tpl'));
	}

	public function getTagItemAtom($r)
	{
		$tag_item = new Dase_DBO_TagItem;
		$tag_item->load($r->get('tag_item_id'));
		if ($tag_item->tag_id != $this->tag->id) {
			$r->renderAtomError(404);
		} 
		$r->renderResponse($tag_item->asAtom());
	}

	public function getTagItem($r)
	{
		$u = $r->getUser();
		$tag_ascii_id = $r->get('tag_ascii_id');
		$tag_item_id = $r->get('tag_item_id');
		$http_pw = $u->getHttpPassword();
		$t = new Dase_Template($r);
		//$t->assign('item',Dase_Atom_Feed::retrieve(APP_ROOT.'/tag/'.$u->eid.'/'.$tag_ascii_id.'/'.$tag_item_id.'?format=atom',$u->eid,$http_pw));
		$t->assign('item',Dase_Atom_Feed::retrieve(APP_ROOT.'/tag/item/'.$this->tag->id.'/'.$tag_item_id.'?format=atom',$u->eid,$http_pw));
		$r->renderResponse($t->fetch('item/display.tpl'));
	}

	public function postToMetadata($r)
	{
		$user = $r->getUser();
		if (!$user->can('admin',$this->tag)) {
			$r->renderError(401,'cannot post tag metadata');
		}
		$att_ascii = $r->get('ascii_id');
		foreach ($this->tag->getTagItems() as $tag_item) {
			$item = $tag_item->getItem();
			foreach ($r->get('value',true) as $val) {
				$item->setValue($att_ascii,$val);
			}
			$item->buildSearchIndex();
		}
		$r->renderRedirect('tag/'.$user->eid.'/'.$this->tag->ascii_id.'/list');
	}

	public function postToTag($r) 
	{
		$tag = $this->tag;
		$u = $r->getUser();
		$u->expireDataCache();
		if (!$u->can('write',$tag)) {
			$r->renderError(401);
		}
		$item_uniques_array = explode(',',$r->get('item_uniques'));
		$num = count($item_uniques_array);
		foreach ($item_uniques_array as $item_unique) {
			$tag->addItem($item_unique);
		}
		$this->tag->updateItemCount();
		$r->response_mime_type = 'text/plain';
		$r->renderResponse("added $num items to $tag->name");
	}

	public function postToTagExpunger($r) 
	{
		$tag = $this->tag;
		$u = $r->getUser();
		$u->expireDataCache();
		if (!$u->can('write',$tag)) {
			$r->renderError(401);
		}
		try {
			$tag->expunge();
		} catch (Exception $e) {
			$r->renderError(400,$e->getMessage());
		}
		$params['msg'] = 'successfully deleted set';
		$r->renderRedirect("collections",$params);
	}

	public function deleteTagItems($r) 
	{
		//move some of this into model
		$tag = $this->tag;
		$u = $r->getUser();
		$u->expireDataCache();
		if (!$u->can('write',$tag)) {
			$r->renderError(401,'user does not have write privileges');
		}
		$item_uniques_array = explode(',',$r->get('uniques'));
		$num = count($item_uniques_array);
		foreach ($item_uniques_array as $item_unique) {
			$tag->removeItem($item_unique);
		}
		$tag->resortTagItems();
		$tag->updateItemCount();
		$r->response_mime_type = 'text/plain';
		$r->renderResponse("removed $num items from $tag->name");
	}

	public function getEdit($r)
	{
		$r->response_mime_type = 'application/atom+xml';
		$entry = new Dase_Atom_Entry_Set;
		$this->tag->injectAtomEntryData($entry);
		$r->renderResponse($entry->asXml());
	}

	public function putEdit($r)
	{
		$user = $r->getUser('http');
		if (!$user->can('write',$this->tag)) {
			$r->renderError(401,'cannot update set');
		}
		$content_type = $r->getContentType();
		if ('application/atom+xml;type=entry' == $content_type ||
			'application/atom+xml' == $content_type
		) {
			$raw_input = file_get_contents("php://input");
			$client_md5 = $r->getHeader('Content-MD5');
			//if Content-MD5 header isn't set, we just won't check
			if ($client_md5 && md5($raw_input) != $client_md5) {
				$r->renderError(412,'md5 does not match');
			}
			$set_entry = Dase_Atom_Entry::load($raw_input);
			if ('set' != $set_entry->entrytype) {
				$r->renderError(400,'must be a set entry');
			}
			$set = $set_entry->update($r);
			if ($set) {
				$r->renderOk();
			}
		}
		$r->renderError(500);
	}

}

