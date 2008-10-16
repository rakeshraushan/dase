<?php

class Dase_Handler_Item extends Dase_Handler
{
	public $resource_map = array( 
		'{collection_ascii_id}/{serial_number}' => 'item',
		'{collection_ascii_id}/{serial_number}/edit' => 'edit_form',
		'{collection_ascii_id}/{serial_number}/media' => 'media',
		'{collection_ascii_id}/{serial_number}/media/count' => 'media_count',
		'{collection_ascii_id}/{serial_number}/metadata' => 'metadata',
		'{collection_ascii_id}/{serial_number}/notes' => 'notes',
		'{collection_ascii_id}/{serial_number}/service' => 'service',
		'{collection_ascii_id}/{serial_number}/status' => 'status',
		'{collection_ascii_id}/{serial_number}/tags' => 'tags',
		'{collection_ascii_id}/{serial_number}/templates' => 'input_templates',
		'{collection_ascii_id}/{serial_number}/notes/{note_id}' => 'note',
	);

	protected function setup($r)
	{
		$this->item = Dase_DBO_Item::get($r->get('collection_ascii_id'),$r->get('serial_number'));
		if (!$this->item) {
			$r->renderError(404);
		}

		//all auth happens in individual methods
	}	

	public function deleteItem($r)
	{
		$user = $r->getUser('http');
		if (!$user->can('write',$this->item)) {
			$r->renderError(401,'user cannot delete this item');
		}
		try {
			$this->item->expunge();
			$r->renderOk('item deleted');
		} catch (Exception $e) {
			$r->renderError(500);
		}
	}
	public function getMediaCount($r)
	{
		$r->renderResponse($this->item->getMediaCount());
	}

	public function getTags($r)
	{
	}

	public function getMediaAtom($r)
	{
		$user = $r->getUser('http');
		if (!$user->can('read',$this->item)) {
			$r->renderError(401,'user cannot read this item');
		}
		$r->renderResponse($this->item->mediaAsAtomFeed());
	}

	public function getItemAtom($r)
	{
		$user = $r->getUser('http');
		if (!$user->can('read',$this->item)) {
			$r->renderError(401,'user cannot read this item');
		}
		if ('entry' == $r->get('type')) {
			$r->renderResponse($this->item->asAtomEntry());
		} else {
			$r->renderResponse($this->item->asAtom());
		}
	}

	public function getItemService($r)
	{
		$user = $r->getUser('http');
		if (!$user->can('read',$this->item)) {
			$r->renderError(401,'user cannot read this item');
		}
		$r->response_mime_type = 'application/atomsvc+xml';
		$r->renderResponse($this->item->getAtomPubServiceDoc());
	}

	public function getItemJson($r)
	{
		$user = $r->getUser();
		if (!$user->can('read',$this->item)) {
			$r->renderError(401,'user cannot read this item');
		}
		$r->renderResponse($this->item->asJson());
	}

	public function getItem($r)
	{
		$user = $r->getUser();
		if (!$user->can('read',$this->item)) {
			$r->renderError(401,'user cannot read this item');
		}
		//a bit inefficient since the setup item get is unecessary, assuming atom feed error reporting
		$t = new Dase_Template($r);
		$feed = Dase_Atom_Feed::retrieve(
			APP_ROOT.'/item/'. $r->get('collection_ascii_id') . '/' . $r->get('serial_number').'.atom',
			$user->eid,$user->getHttpPassword()
		);

		$t->assign('item',$feed);
		$r->renderResponse($t->fetch('item/transform.tpl'));
	}

	public function getEditFormJson($r)
	{
		$user = $r->getUser();
		if (!$user->can('write',$this->item)) {
			$r->renderError(401,'user cannot write this item');
		}
		$r->renderResponse($this->item->getEditFormJson());
	}

	public function getInputTemplates($r)
	{
		$t = new Dase_Template($r);
		$r->renderResponse($t->fetch('item/jstemplates.tpl'));
	}

	public function deleteNote($r)
	{
		$user = $r->getUser();
		if (!$user->can('read',$this->item)) {
			$r->renderError(401,'user cannot read this item');
		}
		$note = new Dase_DBO_Comment;
		$note->load($r->get('note_id'));
		if ($user->eid == $note->updated_by_eid) {
			$note->delete();
		}
		$this->item->buildSearchIndex();
		$r->renderResponse('deleted note '.$note->id);
	}

	public function getStatusJson($r)
	{
		$user = $r->getUser();
		if (!$user->can('read',$this->item)) {
			$r->renderError(401,'user cannot read this item');
		}
		$r->renderResponse($this->item->statusAsJson());
	}

	public function putStatus($r)
	{
		$user = $r->getUser();
		if (!$user->can('write',$this->item)) {
			$r->renderError(401,'cannot write for put');
		}
		$status = trim(file_get_contents("php://input"));

		if (in_array($status,array('public','draft','delete','archive'))) {
			$this->item->status = $status;
		} else {
			$r->renderError(406,'not an acceptable status string');
		}
		$this->item->update();
		$r->renderResponse('status updated');
	}

	public function postToNotes($r)
	{
		$user = $r->getUser();
		if (!$user->can('read',$this->item)) {
			$r->renderError(401,'cannot read this item');
		}
		//auth: anyone can post to an item they can read
		$fp = fopen("php://input", "rb");
		$bits = NULL;
		while(!feof($fp)) {
			$bits .= fread($fp, 4096);
		}
		fclose($fp);
		$this->item->addComment($bits,$user->eid);
		$this->item->buildSearchIndex();
		$r->renderResponse('added content: '.$bits);
	}

	public function postToMetadata($r)
	{
		$user = $r->getUser();
		if (!$user->can('write',$this->item)) {
			$r->renderError(401,'cannot post to metadata');
		}
		$att_ascii = $r->get('ascii_id');
		foreach ($r->get('value',true) as $val) {
			$this->item->setValue($att_ascii,$val);
		}
		$this->item->buildSearchIndex();
		$r->renderResponse('added metadata');
	}

	public function putItem($r)
	{
		$user = $r->getUser('http');
		if (!$user->can('write',$this->item)) {
			$r->renderError(401,'cannot update item');
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
			$item_entry = Dase_Atom_Entry::load($raw_input);
			if ('item' != $item_entry->entrytype) {
				//	$item_entry->setEntryType('item');
				$r->renderError(400,'must be an item entry');
			}
			$item = $item_entry->update($r);
			if ($item) {
				$r->renderOk();
			}
		}
		$r->renderError(500);
	}

	public function getMetadataJson($r)
	{
		$meta =	$this->item->getMetadata();
		$r->renderResponse(Dase_Json::get($meta));
	}

	public function getNotesJson($r)
	{
		$user = $r->getUser();
		if (!$user->can('read',$this->item)) {
			$r->renderError(401,'cannot post media to this item');
		}
		$r->renderResponse($this->item->getCommentsJson());
	}

	public function postToMedia($r) 
	{
		$user = $r->getUser('http');
		if (!$user->can('write',$this->item)) {
			$r->renderError(401,'cannot post media to this item');
		}
		$item = $this->item;
		$coll = $item->getCollection();
		$types = array('image/*','audio/*','video/*','application/pdf');
		if(!isset($_SERVER['CONTENT_LENGTH']) || !isset($_SERVER['CONTENT_TYPE'])) {
			$r->renderError(411,'missing content length');
		}
		//clean this up (prob from wordpress)
		//415 if unsupported?
		$content_type = $r->getContentType();
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

		if ( isset( $_SERVER['HTTP_SLUG'] ) ) {
			$title = $_SERVER['HTTP_SLUG'];
		} elseif ( isset( $_SERVER['HTTP_TITLE'] ) ) {
			$title =  $_SERVER['HTTP_TITLE'];
		} else {
			$title = $item->serial_number;
		}
		$slug = Dase_Util::dirify($title);

		$item->setValue('title',$title);

		$upload_dir = Dase_Config::get('path_to_media').'/'.$coll->ascii_id.'/uploaded_files';
		if (!file_exists($upload_dir)) {
			$r->renderError(401,'missing upload directory '.$upload_dir);
		}

		$ext = $subtype;
		$new_file = $upload_dir.'/'.$item->serial_number.'.'.$ext;

		$ifp = @ fopen( $new_file, 'wb' );
		if (!$ifp) {
			$r->renderError(500);
		}

		@fwrite( $ifp, $bits );
		fclose( $ifp );
		// Set correct file permissions
		@ chmod( $new_file,0644);

		try {
			$file = Dase_File::newFile($new_file,$type);

			//this'll create thumbnail, viewitem, and any derivatives
			//then return the Dase_DBO_MediaFile for the original
			$media_file = $file->addToCollection($item,false);  //set 2nd param to true to test for dups
		} catch(Exception $e) {
			Dase_Log::debug('error',$e->getMessage());
			$r->renderError(500,'could not ingest media file ('.$e->getMessage().')');
		}
		$item->expireCaches();
		$item->buildSearchIndex();
		//the returned atom entry links to derivs!
		$mle_url = APP_ROOT .'/media/'.$media_file->p_collection_ascii_id.'/'.$media_file->size.'/'.$media_file->p_serial_number.'.atom';
		header("Location:". $mle_url,TRUE,201);
		$r->response_mime_type = 'application/atom+xml';
		$r->renderResponse($media_file->asAtom());
	}

	public function getServiceTxt($r)
	{
		$user = $r->getUser();
		if (!$user->can('read',$this->item)) {
			$r->renderError(401,'cannot read item');
		}
		$this->getService($r);
	}

	public function getService($r)
	{
		$user = $r->getUser('http');
		if (!$user->can('read',$this->item)) {
			$r->renderError(401,'cannot read this item service document');
		}
		$r->response_mime_type = 'application/atomsvc+xml';
		$r->renderResponse($this->item->getAtompubServiceDoc());
	}
}

