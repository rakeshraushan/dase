<?php

class Dase_Handler_Admin extends Dase_Handler
{
	public $collection;
	public $resource_map = array(
		'{collection_ascii_id}' => 'settings',
		'{collection_ascii_id}/archive' => 'archive',
		'{collection_ascii_id}/remote_acl' => 'remote_acl',
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

	protected function setup($request)
	{
		$this->collection = Dase_DBO_Collection::get($request->get('collection_ascii_id'));
		if (!$this->collection) {
			$request->renderError(404);
		}
		//todo: this should be in individual methods
		//if ('uploader' == $request->resource) {
		//	$request->can_redirect_to_login = false;
		//}
		$this->user = $request->getUser();
		if (!$this->user->can('admin','collection',$this->collection)) {
			$request->renderError(401);
		}
	}

	public function getSettings($request)
	{
		$tpl = new Dase_Template($request);
		$tpl->assign('user',$this->user);
		$tpl->assign('collection',$this->collection);
		$request->renderResponse($tpl->fetch('admin/settings.tpl'));
	}

	public function getAttributes($request)
	{
		$tpl = new Dase_Template($request);
		$tpl->assign('user',$this->user);
		$tpl->assign('collection',$this->collection);
		$tpl->assign('attributes',$this->collection->getAttributes());
		$request->renderResponse($tpl->fetch('admin/attributes.tpl'));
	}

	public function getItemTypes($request)
	{
		$tpl = new Dase_Template($request);
		$tpl->assign('user',$this->user);
		$tpl->assign('collection',$this->collection);
		$tpl->assign('item_types',$this->collection->getItemTypes());
		$request->renderResponse($tpl->fetch('admin/item_types.tpl'));
	}

	public function getManagers($request)
	{
		$tpl = new Dase_Template($request);
		$tpl->assign('user',$this->user);
		$tpl->assign('collection',$this->collection);
		$tpl->assign('managers',$this->collection->getManagers());
		$request->renderResponse($tpl->fetch('admin/managers.tpl'));
	}

	public function postToManagers($request)
	{
		if (!$request->has('auth_level')) {
			$params['msg'] = 'You must select an Authorization Level';
			$request->renderRedirect('admin/'.$this->collection->ascii_id.'/managers',$params);
		}
		if (!$request->has('dase_user_eid')) {
			$params['msg'] = 'You must enter an EID';
			$request->renderRedirect('admin/'.$this->collection->ascii_id.'/managers',$params);
		}
		if (!Dase_DBO_DaseUser::get($request->get('dase_user_eid'))) {
			$params['msg'] = 'User '.$request->get('dase_user_eid').' does not yet exist';
			$request->renderRedirect('admin/'.$this->collection->ascii_id.'/managers',$params);
		}
		$mgr = new Dase_DBO_CollectionManager;
		$mgr->dase_user_eid = $request->get('dase_user_eid');
		$mgr->auth_level = $request->get('auth_level');
		$mgr->collection_ascii_id = $this->collection->ascii_id;
		$mgr->created = date(DATE_ATOM);
		try {
			$mgr->insert();
			$params['msg'] = 'success!';
		} catch (Exception $e) {
			$params['msg'] = 'there was a problem:'.$e->getMessage();;
		}
		$request->renderRedirect('admin/'.$this->collection->ascii_id.'/managers',$params);
	}

	public function getUploader($request)
	{
		$tpl = new Dase_Template($request);
		$tpl->assign('user',$this->user);
		$tpl->assign('collection',$this->collection);

		$tpl->assign('recent_uploads_url',APP_ROOT.'/user/'.$this->user->eid.'/'.$this->collection->ascii_id.'/recent.atom?limit=10&auth=http');
		$tpl->assign('recent_uploads',Dase_Atom_Feed::retrieve(APP_ROOT.'/user/'.$this->user->eid.'/'.$this->collection->ascii_id.'/recent.atom?limit=10&auth=http',$this->user->eid,$this->user->getHttpPassword()));
		if ($request->has('prev_serial_number')) {
			$tpl->assign('prev_serial_number',$request->get('prev_serial_number'));
		}
		$tpl->assign('num',$request->get('num')+1);
		$request->renderResponse($tpl->fetch('admin/uploader.tpl'));
	}

	public function postToUploader($request)
	{
		//todo: check ppd?
		//todo: 'compose' this as series of atompub posts
		$num = $request->get('num');
		$input_name = 'uploader_'.$num.'_file';
		if (
			isset($_FILES[$input_name]) && 
			is_file($_FILES[$input_name]['tmp_name'])
		) {
			$name = $_FILES[$input_name]['name'];
			$path = $_FILES[$input_name]['tmp_name'];
			$type = $_FILES[$input_name]['type'];
			Dase_Log::info('uploaded file '.$name.' type: '.$type);
			try {
				$u = new Dase_Upload(Dase_File::newFile($path,$type,$name),$this->collection);
				$ser_num = $u->createItem($request->getUser()->eid);
				$u->ingest();
				$u->setTitle($name);
				$u->buildSearchIndex();
			} catch(Exception $e) {
				$error_msg = $e->getMessage();
				Dase_Log::info($error_msg);
				header("HTTP/1.1 400 Bad Request");
				$data['status'] = 'bad request';
				$data['message'] = $error_msg;
				$data['num'] = $num;
				$request->renderResponse(Dase_Json::get($data));
			}
		} else {
			$request->renderError(400,'could not upload file');
		}
		$params['status'] = 'ok';
		$params['num'] = $num;
		$params['message'] = 'ok';
		$params['filename'] = $name;
		$params['filesize'] = $u->getFileSize();
		$params['filetype'] = $u->getFiletype();
		$params['title'] = $u->getTitle();
		$params['item_url'] = $u->getItemUrl();
		$params['thumbnail_url'] = $u->getThumbnailUrl();
		Dase_Log::debug(join('|',$params));
		$request->renderRedirect('admin/'.$this->collection->ascii_id.'/upload/status',$params);
	}

	public function getUploadStatus($request)
	{
		foreach ($this->upload_responses as $f) {
			$data[$f]=$request->get($f);
		}
		//it is json, but needs to be rendered as text/html
		//since it is going to an iframe
		$request->renderResponse(Dase_Json::get($data));
	}

	public function getArchive($request) 
	{
		$archive = CACHE_DIR.$this->collection->ascii_id.'_'.time();
		file_put_contents($archive,$this->collection->asAtomArchive());
		$request->serveFile($archive,'text/plain',true);
	}

	public function postToIndexer($request) 
	{
		$this->collection->buildSearchIndex();
		$params['msg'] = "rebuilt indexes for $this->collection->collection_name";
		$request->renderRedirect('',$params);
	}
}

