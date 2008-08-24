<?php

class Dase_Handler_Item extends Dase_Handler
{
	public $resource_map = array( 
		'{collection_ascii_id}/{serial_number}' => 'item',
		'{collection_ascii_id}/{serial_number}/micro' => 'microformat',
		'{collection_ascii_id}/{serial_number}/edit' => 'edit_form',
		'{collection_ascii_id}/{serial_number}/media' => 'media',
		'{collection_ascii_id}/{serial_number}/media/count' => 'media_count',
		'{collection_ascii_id}/{serial_number}/metadata' => 'metadata',
		'{collection_ascii_id}/{serial_number}/notes' => 'notes',
		'{collection_ascii_id}/{serial_number}/service' => 'service',
		'{collection_ascii_id}/{serial_number}/status' => 'status',
		'{collection_ascii_id}/{serial_number}/templates' => 'input_templates',
		'{collection_ascii_id}/{serial_number}/notes/{note_id}' => 'note',
	);

	protected function setup($request)
	{
		$this->item = Dase_DBO_Item::get($request->get('collection_ascii_id'),$request->get('serial_number'));
		if (!$this->item) {
			$request->renderError(404);
		}

		//todo: work on better auth mapping
		if ('media_count' != $request->resource) { 
			if ('service' == $request->resource || 
				'atom' == $request->format ||
				('media' == $request->resource && 'post' == $request->method) ||
				('item' == $request->resource && 'delete' == $request->method) 
			) {
				$this->user = $request->getUser('http');
			} else {
				$this->user = $request->getUser();
				if (!$this->user->can('read','item',$this->item)) {
					$request->renderError(401,'user cannot read this item');
				}
			}
		}
	}	

	public function deleteItem($request)
	{
		if (!is_writeable(Dase_Config::get('graveyard'))) {
			$request->renderError(500);
		}
		try {
			$this->item->expunge();
			$request->renderOk('item deleted');
		} catch (Exception $e) {
			$request->renderError(500);
		}
	}
	public function getMediaCount($request)
	{
		$request->renderResponse($this->item->getMediaCount());
	}

	public function getMicroformat($request)
	{
		$request->renderResponse($this->item->asMicroformat());
	}

	public function getMediaAtom($request)
	{
		$request->renderResponse($this->item->mediaAsAtomFeed());
	}

	public function getItemAtom($request)
	{
		$request->renderResponse($this->item->asAtom());
	}

	public function getItemService($request)
	{
		$request->response_mime_type = 'application/atomsvc+xml';
		$request->renderResponse($this->item->getAtomPubServiceDoc());
	}

	public function getItemJson($request)
	{
		$request->renderResponse($this->item->asJson());
	}

	public function getItem($request)
	{
		//a bit inefficient since the setup item get is unecessary, assuming atom feed error reporting
		$t = new Dase_Template($request);
		$feed = Dase_Atom_Feed::retrieve(
			APP_ROOT.'/item/'. $request->get('collection_ascii_id') . '/' . $request->get('serial_number').'.atom',
			$this->user->eid,$this->user->getHttpPassword()
		);

		$hist = new Dase_DBO_UserHistory;
		$hist->eid = $request->getUser()->eid;
		$hist->href = APP_ROOT.'/'.$request->url;
		$hist->title = $feed->getTitle();
		//$hist->summary = $feed->getSearchEcho();
		$hist->type = 'item_view';
		$hist->updated = date(DATE_ATOM);
		$hist->insert();

		$t->assign('item',$feed);
		$request->renderResponse($t->fetch('item/transform.tpl'));
	}

	public function getEditFormJson($request)
	{
		$request->renderResponse($this->item->getEditFormJson());
	}

	public function getInputTemplates($request)
	{
		$t = new Dase_Template($request);
		$request->renderResponse($t->fetch('item/jstemplates.tpl'));
	}

	public function deleteNote($request)
	{
		$note = new Dase_DBO_Content;
		$note->load($request->get('note_id'));
		if ($this->user->eid == $note->updated_by_eid) {
			$note->delete();
		}
		$this->item->buildSearchIndex();
		$request->renderResponse('deleted note '.$note->id);
	}

	public function getStatusJson($request)
	{
		$request->renderResponse($this->item->statusAsJson());
	}

	public function putStatus($request)
	{
		if (!$this->user->can('write','item',$this->item)) {
			$request->renderError(401,'cannot write for put');
		}
		$status = trim(file_get_contents("php://input"));

		if (in_array($status,array('public','draft','delete','archive'))) {
			$this->item->status = $status;
		} else {
			$request->renderError(406,'not an acceptable status string');
		}
		$this->item->update();
		$request->renderResponse('status updated');
	}

	public function postToNotes($request)
	{
		//auth: anyone can post to an item they can read
		$fp = fopen("php://input", "rb");
		$bits = NULL;
		while(!feof($fp)) {
			$bits .= fread($fp, 4096);
		}
		fclose($fp);
		$this->item->addContent($bits,$this->user->eid);
		$this->item->buildSearchIndex();
		$request->renderResponse('added content: '.$bits);
	}

	public function postToMetadata($request)
	{
		if (!$this->user->can('write','item',$this->item)) {
			$request->renderError(401,'cannot post to metadata');
		}
		$att_ascii = $request->get('ascii_id');
		foreach ($request->get('value',true) as $val) {
			$this->item->setValue($att_ascii,$val);
		}
		$this->item->buildSearchIndex();
		$request->renderResponse('added metadata');
	}

	public function putItem($request)
	{
		$content_type = $request->getContentType();
		if ('application/atom+xml;type=entry' == $content_type ||
			'application/atom+xml' == $content_type
		) {
			$raw_input = file_get_contents("php://input");
			$client_md5 = $request->getHeader('Content-MD5');
			//if Content-MD5 header isn't set, we just won't check
			if ($client_md5 && md5($raw_input) != $client_md5) {
				$request->renderError(412,'md5 does not match');
			}
			$item_entry = Dase_Atom_Entry::load($raw_input);
			if ('item' != $item_entry->entrytype) {
				//	$item_entry->setEntryType('item');
				$request->renderError(400,'must be an item entry');
			}
			$item = $item_entry->update($request);
			if ($item) {
				$request->renderOk();
			}
		}
		$request->renderError(500);
	}

	public function getMetadataJson($request)
	{
		$meta =	$this->item->getMetadata();
		$request->renderResponse(Dase_Json::get($meta));
	}

	public function getNotesJson($request)
	{
		$request->renderResponse($this->item->getContentsJson());
	}

	public function postToMedia($request) 
	{
		$item = $this->item;
		$coll = $item->getCollection();
		$this->user = $request->getUser('http');
		if (!$this->user->can('write','item',$item)) {
			$request->renderError(401,'user cannot write media file');
		}
		$types = array('image/*','audio/*','video/*','application/pdf');
		if(!isset($_SERVER['CONTENT_LENGTH']) || !isset($_SERVER['CONTENT_TYPE'])) {
			$request->renderError(411,'missing content length');
		}
		//clean this up (prob from wordpress)
		$content_type = $request->getContentType();
		list($type,$subtype) = explode('/',$content_type);
		list($subtype) = explode(";",$subtype); // strip MIME parameters
		foreach($types as $t) {
			list($acceptedType,$acceptedSubtype) = explode('/',$t);
			if($acceptedType == '*' || $acceptedType == $type) {
				if($acceptedSubtype == '*' || $acceptedSubtype == $subtype)
					$type = $type . "/" . $subtype;
			}
		}
		$fp = fopen("php://input", "rb");
		$bits = NULL;
		while(!feof($fp)) {
			$bits .= fread($fp, 4096);
		}
		fclose($fp);

		$coll = $item->getCollection();

		$slug = '';
		if ( isset( $_SERVER['HTTP_SLUG'] ) ) {
			$slug = Dase_Util::dirify( $_SERVER['HTTP_SLUG'] );
		} elseif ( isset( $_SERVER['HTTP_TITLE'] ) ) {
			$slug = Dase_Util::dirify( $_SERVER['HTTP_TITLE'] );
		} else {
			$slug = $item->serial_number;
		}

		$upload_dir = Dase_Config::get('path_to_media').'/'.$coll->ascii_id.'/uploaded_files';
		if (!file_exists($upload_dir)) {
			$request->renderError(401,'missing upload directory');
		}

		$ext = $subtype;
		$new_file = $upload_dir.'/'.$item->serial_number.'.'.$ext;

		$ifp = @ fopen( $new_file, 'wb' );
		if (!$ifp) {
			$request->renderError(500);
		}

		@fwrite( $ifp, $bits );
		fclose( $ifp );
		// Set correct file permissions
		@ chmod( $new_file,0644);

		try {
			$file = Dase_File::newFile($new_file,$type);

			//this'll create thumbnail, viewitem, and any derivatives
			//then return the Dase_DBO_MediaFile for the original
			$media_file = $file->addToCollection($item->serial_number,$item->serial_number,$coll,false);  //set 4th param to true to test for dups
		} catch(Exception $e) {
			Dase_Log::debug('error',$e->getMessage());
			$request->renderError(500,'could not ingest file ('.$e->getMessage().')');
		}
		//the returned atom entry links to derivs!
		$mle_url = APP_ROOT .'/media/'.$media_file->p_collection_ascii_id.'/'.$media_file->size.'/'.$media_file->p_serial_number.'.atom';
		header("Location:". $mle_url,TRUE,201);
		$request->response_mime_type = 'application/atom+xml';
		$request->renderResponse($media_file->asAtom());
	}

	public function getServiceTxt($request)
	{
		$this->getService($request);
	}

	public function getService($request)
	{
		$request->response_mime_type = 'application/atomsvc+xml';
		$request->renderResponse($this->item->getAtompubServiceDoc());
	}
}

