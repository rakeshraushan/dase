<?php

class Dase_Handler_Item extends Dase_Handler
{
	public $resource_map = array( 
		'{collection_ascii_id}/{serial_number}' => 'item',
		'{collection_ascii_id}/{serial_number}/media' => 'media',
		'{collection_ascii_id}/{serial_number}/media/count' => 'media_count',
		//used for get and post
		'{collection_ascii_id}/{serial_number}/metadata' => 'metadata',
		//used for put and delete
		'{collection_ascii_id}/{serial_number}/metadata/{value_id}' => 'metadata',
		'{collection_ascii_id}/{serial_number}/comments' => 'comments',
		'{collection_ascii_id}/{serial_number}/content' => 'content',
		'{collection_ascii_id}/{serial_number}/service' => 'service',
		'{collection_ascii_id}/{serial_number}/status' => 'status',
		'{collection_ascii_id}/{serial_number}/tags' => 'tags',
		'{collection_ascii_id}/{serial_number}/templates' => 'input_templates',
		'{collection_ascii_id}/{serial_number}/comments/{comment_id}' => 'comment',
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
		$user = $r->getUser('service');
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
		/*
		$user = $r->getUser('http');
		if (!$user->can('read',$this->item)) {
			$r->renderError(401,'user cannot read this item');
		}
		 */
		if ('feed' == $r->get('type')) {
			$r->renderResponse($this->item->asAtom());
		} else {
			$r->renderResponse($this->item->asAtomEntry());
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

	/** this is for ajax retrieval of content versions */
	public function getContentJson($r)
	{
		$user = $r->getUser();
		if (!$user->can('read',$this->item)) {
			$r->renderError(401,'user cannot read this item');
		}
		$r->renderResponse($this->item->getContentJson());
	}

	/** this is for simply getting the content 
	 * note that type MUST be a mime_type
	 * */
	public function getContent($r)
	{
		$user = $r->getUser();
		if (!$user->can('read',$this->item)) {
			$r->renderError(401,'user cannot read this item');
		}
		$cont = $this->item->getContents();	
		if ('xhtml' == $cont->type) {
			$mime_type = 'application/xhtml+xml';
		} elseif ('html' == $cont->type) {
			$mime_type = 'text/html';
		} elseif ('text' == $cont->type) {
			$mime_type = 'text/plain';
		} else {
			$mime_type = $cont->type;
		}
		$r->response_mime_type = $mime_type;
		$r->renderResponse($cont->text);
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
			APP_ROOT.'/item/'. $r->get('collection_ascii_id') . '/' . $r->get('serial_number').'.atom?type=feed',
			$user->eid,$user->getHttpPassword()
		);

		if ($user->can('write',$this->item)) {
			$t->assign('is_admin',1);
		}
		$t->assign('item',$feed);
		$r->renderResponse($t->fetch('item/display.tpl'));
	}

	public function getMetadataJson($r)
	{
		$user = $r->getUser();
		if (!$user->can('write',$this->item)) {
			$r->renderError(401,'user cannot write this item');
		}
		$r->renderResponse($this->item->getMetadataJson());
	}

	public function getInputTemplates($r)
	{
		$t = new Dase_Template($r);
		$r->renderResponse($t->fetch('item/jstemplates.tpl'));
	}

	public function deleteComment($r)
	{
		$user = $r->getUser();
		if (!$user->can('read',$this->item)) {
			$r->renderError(401,'user cannot read this item');
		}
		$comment = new Dase_DBO_Comment;
		$comment->load($r->get('comment_id'));
		if ($user->eid == $comment->updated_by_eid) {
			$comment->delete();
		}
		//todo: I don't think we are indexing comments (??)
		//$this->item->buildSearchIndex();
		$r->renderResponse('deleted comment '.$comment->id);
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

	public function postToComments($r)
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
		$bits = trim($bits);
		$this->item->addComment($bits,$user->eid);
		//comments should NOT be globally searchable
		//$this->item->buildSearchIndex();
		$r->renderResponse('added comment: '.$bits);
	}

	/** this is used to UPDATE an item's content */
	public function postToContent($r)
	{
		$user = $r->getUser();
		if (!$user->can('write',$this->item)) {
			$r->renderError(401,'cannot write to this item');
		}
		$content_type = $r->getContentType();
		if ('application/x-www-form-urlencoded' == $content_type) {
			$content_type = 'text';
			$content = $r->get('content');
		} else {
		//todo: filter this!
			$content = file_get_contents("php://input");
		}
		if ($this->item->setContent($content,$user->eid,$content_type)) {
			$r->renderResponse('added content');
		}
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

	public function putMetadata($r)
	{
		$value_text = file_get_contents("php://input");
		$user = $r->getUser();
		if (!$user->can('write',$this->item)) {
			$r->renderError(401,'cannot put metadata');
		}
		if (!$r->has('value_id')) {
			$r->renderError(400,'missing identifier');
		}
		$value_id = $r->get('value_id');
		$this->item->updateMetadata($r,$value_id,strip_tags($value_text));
		$r->renderResponse($value_id.'|'.$value_text);
	}

	public function deleteMetadata($r)
	{
		$user = $r->getUser();
		if (!$user->can('write',$this->item)) {
			$r->renderError(401,'cannot delete metadata');
		}
		if (!$r->has('value_id')) {
			$r->renderError(400,'missing identifier');
		}
		//try/catch??
		$value_id = $r->get('value_id');
		$this->item->removeMetadata($r,$value_id);
		$r->renderResponse('deleted');
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
			$item_entry = Dase_Atom_Entry::load($raw_input,'item');
			if ('item' != $item_entry->entrytype) {
				$item_entry->setEntryType('item');
				//$r->renderError(400,'must be an item entry');
			}
			$item = $item_entry->update($r);
			if ($item) {
				$r->renderOk();
			}
		}
		$r->renderError(500);
	}

	public function getCommentsJson($r)
	{
		$user = $r->getUser();
		if (!$user->can('read',$this->item)) {
			$r->renderError(401,'cannot read comments on this item');
		}
		//todo: should displayed comments be limited to this user???
		$r->renderResponse($this->item->getCommentsJson($user->eid));
	}

	public function postToMedia($r) 
	{
		$user = $r->getUser('service');
		if (!$user->can('write',$this->item)) {
			$r->renderError(401,'cannot post media to this item');
		}
		$item = $this->item;
		$coll = $item->getCollection();
		if(!isset($_SERVER['CONTENT_LENGTH']) || !isset($_SERVER['CONTENT_TYPE'])) {
			$r->renderError(411,'missing content length');
		}
		$content_type = Dase_Media::isAcceptable($r->getContentType());
		if (!$content_type) {
			$r->renderError(415,'not an accepted media type');
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

		$new_file = $upload_dir.'/'.$item->serial_number;

		$ifp = @ fopen( $new_file, 'wb' );
		if (!$ifp) {
			$r->renderError(500,'cannot write file');
		}

		@fwrite( $ifp, $bits );
		fclose( $ifp );
		// Set correct file permissions
		@ chmod( $new_file,0644);

		try {
			$file = Dase_File::newFile($new_file,$content_type);

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
		$mle_url = APP_ROOT .'/media/'.$media_file->p_collection_ascii_id.'/'.$media_file->p_serial_number.'.atom';
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

