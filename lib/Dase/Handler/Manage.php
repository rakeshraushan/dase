<?php

class Dase_Handler_Manage extends Dase_Handler
{
	public $collection;
	public $resource_map = array(
		'{collection_ascii_id}' => 'uploader',
		'{collection_ascii_id}/archive' => 'archive',
		'{collection_ascii_id}/remote_acl' => 'remote_acl',
		'{collection_ascii_id}/attribute/form' => 'attribute_form',
		'{collection_ascii_id}/attribute/{att_ascii_id}' => 'attribute',
		'{collection_ascii_id}/attribute/{att_ascii_id}/defined_values' => 'attribute_defined_values',
		'{collection_ascii_id}/attributes' => 'attributes',
		'{collection_ascii_id}/item_types' => 'item_types',
		'{collection_ascii_id}/managers' => 'managers',
		'{collection_ascii_id}/settings' => 'settings',
		'{collection_ascii_id}/indexer' => 'indexer',
		'{collection_ascii_id}/uploader' => 'uploader',
		'{collection_ascii_id}/upload/status' => 'upload_status',
		'{collection_ascii_id}/attributes/{filter}' => 'attributes',
	);
	public $upload_responses = array('status','num','message','filename','filesize','filetype','title','item_url','thumbnail_url');

	protected function setup($r)
	{
		$this->collection = Dase_DBO_Collection::get($r->get('collection_ascii_id'));
		if (!$this->collection) {
			$r->renderError(404);
		}
		$this->user = $r->getUser();
		if (!$this->user->can('admin',$this->collection)) {
			$r->renderError(401);
		}
	}

	public function getSettings($r)
	{
		$tpl = new Dase_Template($r);
		$tpl->assign('user',$this->user);
		$tpl->assign('collection',$this->collection);
		$r->renderResponse($tpl->fetch('manage/settings.tpl'));
	}

	public function postToSettings($r)
	{
		$this->collection->collection_name = trim($r->get('collection_name'));
		//uses false because you cannot pass a zero as a value through form (dase framework bug)
		if ('false' == $r->get('is_public')) {
			$this->collection->is_public = 0;
		} else {
			$this->collection->is_public = 1;
		}
		$this->collection->description = trim($r->get('description'));
		$this->collection->visibility = $r->get('visibility');
		$this->collection->update();
		$params['msg'] = "settings updated";
		$this->user->expireDataCache();
		$r->renderRedirect('manage/'.$this->collection->ascii_id.'/settings',$params);
	}

	public function getAttributes($r)
	{
		$tpl = new Dase_Template($r);
		if ($r->has('sort')) {
			$so = $r->get('sort');
		} else {
			$so = 'sort_order';
		}
		$tpl->assign('user',$this->user);
		$tpl->assign('sort',$so);
		$tpl->assign('collection',$this->collection);
		$tpl->assign('attributes',$this->collection->getAttributes($so));
		$r->renderResponse($tpl->fetch('manage/attributes.tpl'));
	}

	public function getAttributesJson($r)
	{
		if ($r->has('sort')) {
			$so = $r->get('sort');
		} else {
			$so = 'sort_order';
		}
		$r->renderResponse($this->collection->getAttributesJson($so));
	}

	public function getAttributeForm($r)
	{
		$tpl = new Dase_Template($r);
		$r->renderResponse($tpl->fetch('manage/attribute_form.tpl'));
	}

	public function postToAttribute($r)
	{
		$att = Dase_DBO_Attribute::get($this->collection->ascii_id,$r->get('att_ascii_id'));
		$params['sort'] = $r->get('sort');
		if ($r->has('method') && ('delete attribute' == $r->get('method'))) {
			$d = $att->attribute_name;
			$count = count($att->getCurrentValues());
			if ($count) {
				$params['msg'] = "sorry, but there are $count values for $att->attribute_name so it cannot be deleted";
				$r->renderRedirect('manage/'.$this->collection->ascii_id.'/attributes',$params);
			}
			$att->expunge();
			$att->resort();
			$params['msg'] = "$d deleted";
			$r->renderRedirect('manage/'.$this->collection->ascii_id.'/attributes',$params);
		}
		$att->attribute_name = $r->get('attribute_name');
		$att->usage_notes = $r->get('usage_notes');
		if ($r->has('is_on_list_display')) {
			$att->is_on_list_display = 1;
		} else {
			$att->is_on_list_display = 0;
		}
		if ($r->has('in_basic_search')) {
			$att->in_basic_search = 1;
		} else {
			$att->in_basic_search = 0;
		}
		if ($r->has('is_public')) {
			$att->is_public = 1;
		} else {
			$att->is_public = 0;
		}
		$att->html_input_type = $r->get('input_type');
		$att->update();
		$att->resort($r->get('sort_after'));
		$params['msg'] = "$att->attribute_name updated";
		$r->renderRedirect('manage/'.$this->collection->ascii_id.'/attributes',$params);
	}

	public function putAttributeDefinedValues($r)
	{
		$att = Dase_DBO_Attribute::get($this->collection->ascii_id,$r->get('att_ascii_id'));
		$def_values = new Dase_DBO_DefinedValue;
		$def_values->attribute_id = $att->id;
		foreach ($def_values->find() as $df) {
			$df->delete();
		}
		$defined_values = trim(file_get_contents("php://input"));
		$pattern = "/[\n;]/";
		$munged_string = preg_replace($pattern,'%',$defined_values);
		$def_value_array = explode('%',$munged_string); 
		foreach ($def_value_array as $df_text) {
			if (trim($df_text)) {
				$def_value = new Dase_DBO_DefinedValue;
				$def_value->value_text = htmlspecialchars(trim($df_text),ENT_NOQUOTES,'UTF-8');
				$def_value->attribute_id = $att->id;
				$def_value->insert();
			}
		}
		$r->response_mime_type = 'application/json';
		$r->renderResponse(Dase_Json::get($def_value_array));
	}


	public function postToAttributes($r)
	{
		$att = new Dase_DBO_Attribute;
		$att->attribute_name = $r->get('attribute_name');
		$att->ascii_id = Dase_Util::dirify($att->attribute_name);
		if (!Dase_DBO_Attribute::get($this->collection->ascii_id,$att->ascii_id)) {
			$att->collection_id = $this->collection->id;
			$att->updated = date(DATE_ATOM);
			$att->sort_order = 999;
			$att->is_on_list_display = 1;
			$att->is_public = 1;
			$att->in_basic_search = 1;
			$att->html_input_type = 'text';
			$att->insert();
			$att->resort();
			$params['msg'] = "added $att->attribute_name";
			$r->renderRedirect('manage/'.$this->collection->ascii_id.'/attributes',$params);
		}
	}


	public function getItemTypes($r)
	{
		$tpl = new Dase_Template($r);
		$tpl->assign('user',$this->user);
		$tpl->assign('collection',$this->collection);
		$tpl->assign('item_types',$this->collection->getItemTypes());
		$r->renderResponse($tpl->fetch('manage/item_types.tpl'));
	}

	public function getManagers($r)
	{
		$tpl = new Dase_Template($r);
		$tpl->assign('user',$this->user);
		$tpl->assign('collection',$this->collection);
		$tpl->assign('managers',$this->collection->getManagers());
		$r->renderResponse($tpl->fetch('manage/managers.tpl'));
	}

	public function postToManagers($r)
	{
		if (!$r->has('auth_level')) {
			$params['msg'] = 'You must select an Authorization Level';
			$r->renderRedirect('manage/'.$this->collection->ascii_id.'/managers',$params);
		}
		if (!$r->has('dase_user_eid')) {
			$params['msg'] = 'You must enter an EID';
			$r->renderRedirect('manage/'.$this->collection->ascii_id.'/managers',$params);
		}
		if (!Dase_DBO_DaseUser::get($r->get('dase_user_eid'))) {
			$params['msg'] = 'User '.$r->get('dase_user_eid').' does not yet exist';
			$r->renderRedirect('manage/'.$this->collection->ascii_id.'/managers',$params);
		}
		$mgr = new Dase_DBO_CollectionManager;
		$mgr->dase_user_eid = $r->get('dase_user_eid');
		$mgr->auth_level = $r->get('auth_level');
		$mgr->collection_ascii_id = $this->collection->ascii_id;
		$mgr->created = date(DATE_ATOM);
		try {
			$mgr->insert();
			$params['msg'] = 'success!';
		} catch (Exception $e) {
			$params['msg'] = 'there was a problem:'.$e->getMessage();;
		}
		$r->renderRedirect('manage/'.$this->collection->ascii_id.'/managers',$params);
	}

	public function getUploader($r)
	{
		$tpl = new Dase_Template($r);
		$tpl->assign('user',$this->user);
		$tpl->assign('collection',$this->collection);

		$tpl->assign('recent_uploads_url',APP_ROOT.'/user/'.$this->user->eid.'/'.$this->collection->ascii_id.'/recent.atom?limit=10&auth=http');
		$tpl->assign('recent_uploads',Dase_Atom_Feed::retrieve(APP_ROOT.'/user/'.$this->user->eid.'/'.$this->collection->ascii_id.'/recent.atom?limit=10&auth=http',$this->user->eid,$this->user->getHttpPassword()));
		if ($r->has('prev_serial_number')) {
			$tpl->assign('prev_serial_number',$r->get('prev_serial_number'));
		}
		$tpl->assign('num',$r->get('num')+1);
		$r->renderResponse($tpl->fetch('manage/uploader.tpl'));
	}

	/** here we create an item, then AtomPub POST a file with the sernum as slug */
	/* (cool, but bandwidth wasteful and slow) */
	public function postToUploader($r)
	{
		//todo: check ppd?
		//todo: 'compose' this as series of atompub posts
		$num = $r->get('num');
		$input_name = 'uploader_'.$num.'_file';
		if (
			isset($_FILES[$input_name]) && 
			is_file($_FILES[$input_name]['tmp_name'])
		) {
			$name = $_FILES[$input_name]['name'];
			$path = $_FILES[$input_name]['tmp_name'];
			$type = $_FILES[$input_name]['type'];
			Dase_Log::info('uploading file '.$name.' type: '.$type);

			$item = Dase_DBO_Item::create($this->collection->ascii_id,null,$this->user->eid);
			$item->setValue('title',$name);

			/*
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, APP_ROOT.'/media/'.$this->collection->ascii_id.'?auth=http');
			$upload = file_get_contents($path);
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
			curl_setopt($ch, CURLOPT_POSTFIELDS, $upload);
			curl_setopt($ch, CURLOPT_USERPWD,$this->user->eid.':'.$this->user->getHttpPassword());
			$str  = array(
				"Slug: $item->serial_number",
				"Content-type: $type"
			);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $str);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			Dase_Log::debug(curl_exec($ch));
			curl_close($ch);  
			 */
			$file = Dase_File::newFile($path,$type);
			//this'll create thumbnail, viewitem, and any derivatives
			$media_file = $file->addToCollection($item,false);
			$item->buildSearchIndex();
		} else {
			$r->renderError(400,'could not upload file');
		}
		$params['status'] = 'ok';
		$params['num'] = $num;
		$params['message'] = 'ok';
		$params['filename'] = $name;
		$params['filesize'] = filesize($path);
		$params['filetype'] = $type;
		$params['title'] = $item->serial_number;
		$params['item_url'] = $item->getBaseUrl();
		$params['thumbnail_url'] = $item->getMediaUrl('thumbnail');
		Dase_Log::debug(join('|',$params));
		$r->renderRedirect('manage/'.$this->collection->ascii_id.'/upload/status',$params);
	}

	public function getUploadStatus($r)
	{
		foreach ($this->upload_responses as $f) {
			$data[$f]=$r->get($f);
		}
		//it is json, but needs to be rendered as text/html
		//since it is going to an iframe
		$r->renderResponse(Dase_Json::get($data));
	}

	public function getArchive($r) 
	{
		$archive = CACHE_DIR.$this->collection->ascii_id.'_'.time();
		file_put_contents($archive,$this->collection->asAtomArchive());
		$r->serveFile($archive,'text/plain',true);
	}

	public function postToIndexer($r) 
	{
		$this->collection->buildSearchIndex();
		$params['msg'] = "rebuilt indexes for $this->collection->collection_name";
		$r->renderRedirect('',$params);
	}
}

