<?php

class Dase_Handler_Item extends Dase_Handler
{
	public $resource_map = array( 
		'{collection_ascii_id}/{serial_number}' => 'item',
		'{collection_ascii_id}/{serial_number}/ingester' => 'ingester',
		'{collection_ascii_id}/{serial_number}/ping' => 'ping',
		'{collection_ascii_id}/{serial_number}/media' => 'media',
		'{collection_ascii_id}/{serial_number}/media/count' => 'media_count',
		//used for get and post
		//also atom categories PUT
		'{collection_ascii_id}/{serial_number}/metadata' => 'metadata',
		//used for put and delete
		'{collection_ascii_id}/{serial_number}/metadata/title' => 'title',
		'{collection_ascii_id}/{serial_number}/metadata/{value_id}' => 'metadata_value',
		'{collection_ascii_id}/{serial_number}/comments' => 'comments',
		'{collection_ascii_id}/{serial_number}/parents' => 'parents',
		'{collection_ascii_id}/{serial_number}/content' => 'content',
		'{collection_ascii_id}/{serial_number}/service' => 'service',
		'{collection_ascii_id}/{serial_number}/status' => 'status',
		'{collection_ascii_id}/{serial_number}/item_type' => 'item_type',
		'{collection_ascii_id}/{serial_number}/tags' => 'tags',
		'{collection_ascii_id}/{serial_number}/comments/{comment_id}' => 'comment',
	);

	protected function setup($r)
	{
		$this->item = Dase_DBO_Item::get($this->db,$r->get('collection_ascii_id'),$r->get('serial_number'));
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
			$this->item->expunge($this->path_to_media);
			$r->renderOk('item deleted');
		} catch (Exception $e) {
			$r->renderError(500);
		}
	}
	public function getMediaCount($r)
	{
		$r->renderResponse($this->item->getMediaCount());
	}

	public function getPing($r)
	{
		$r->renderOk('item exists');
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
		$r->renderResponse($this->item->mediaAsAtomFeed($r->app_root));
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
			$r->renderResponse($this->item->asAtom($r->app_root));
		} else {
			$r->renderResponse($this->item->asAtomEntry($r->app_root));
		}
	}

	public function getItemService($r)
	{
		$user = $r->getUser('http');
		if (!$user->can('read',$this->item)) {
			$r->renderError(401,'user cannot read this item');
		}
		$r->response_mime_type = 'application/atomsvc+xml';
		$r->renderResponse($this->item->getAtomPubServiceDoc($r->app_root));
	}

	/** this is for ajax retrieval of content versions */
	public function getContentJson($r)
	{
		$user = $r->getUser();
		if (!$user->can('read',$this->item)) {
			$r->renderError(401,'user cannot read this item');
		}
		$r->renderResponse($this->item->getContentJson(true));
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
		//$r->renderResponse($this->item->asJson());
		$r->renderResponse($this->item->asAtomJson($r->app_root));
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
			$r->app_root.'/item/'. $r->get('collection_ascii_id') . '/' . $r->get('serial_number').'.atom?type=feed',
			$user->eid,$user->getHttpPassword($r->retrieve('config')->getAuth('token'))
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
		$r->renderResponse($this->item->getMetadataJson($r->app_root));
	}

	public function getMetadataTxt($r)
	{
		$user = $r->getUser('http');
		if (!$user->can('read',$this->item)) {
			$r->renderError(401,'user cannot read this item');
		}
		if ($r->has('display')) {
			$meta = $this->item->getMetadata($r->get('display'));
			if (isset($meta[0]) && isset($meta[0]['value_text'])) {
				$r->renderResponse($meta[0]['value_text']);
			} else {
				$r->renderError(404);
			}
		} else {
			$output = '';
			foreach ($this->item->getMetadata() as $meta) {
				$output .=  $meta['ascii_id'].':'.$meta['value_text']."\n";
			}
			$r->renderResponse($output);
		}
	}

	public function deleteComment($r)
	{
		$user = $r->getUser();
		if (!$user->can('read',$this->item)) {
			$r->renderError(401,'user cannot read this item');
		}
		$comment = new Dase_DBO_Comment($this->db);
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
		$status = trim($r->getBody());

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
		$bits = $r->getBody();
		$this->item->addComment($bits,$user->eid);
		//comments should NOT be globally searchable
		//$this->item->buildSearchIndex();
		$r->renderResponse('added comment: '.$bits);
	}

	/** this is used to UPDATE an item's content */
	public function postToContent($r)
	{
		$user = $r->getUser();
		$this->_updateContent($r,$user);
	}

	/** this is used to UPDATE an item's content */
	public function putContent($r)
	{
		$user = $r->getUser('http');
		$this->_updateContent($r,$user);
	}

	private function _updateContent($r,$user)
	{
		if (!$user->can('write',$this->item)) {
			$r->renderError(401,'cannot write to this item');
		}
		$content_type = $r->getContentType();
		if ('application/x-www-form-urlencoded' == $content_type) {
			$content_type = 'text';
			$content = $r->get('content');
		} else {
		//todo: filter this!
			$content = $r->getBody();
		}
		if ($this->item->setContent($content,$user->eid,$content_type)) {
			$r->renderResponse('content updated');
		}
	}

	/** allows us to simply POST the uri of a (legit) parent */
	public function postToParents($r)
	{
		$sernum = $r->get('serial_number');
		$coll = $r->get('collection_ascii_id');

		$user = $r->getUser();
		if (!$user->can('write',$this->item)) {
			$r->renderError(401,'cannot write to this item');
		}
		$content_type = $r->getContentType();
		if ('text/uri-list' == $content_type) {
			$uri = trim($r->getBody());
			$parent = Dase_DBO_Item::getByUrl($this->db,$uri);
			if ($parent) {
				$itr = Dase_DBO_ItemTypeRelation::getByItemSerialNumbers(
					$this->db,$coll,$parent->serial_number,$sernum
				);
			}
            if ($itr) {
                $item_relation = new Dase_DBO_ItemRelation($this->db);
                $item_relation->collection_ascii_id = $coll;
                $item_relation->parent_serial_number = $parent->serial_number;
                $item_relation->child_serial_number = $sernum;
                if (!$item_relation->findOne()) {
                    $item_relation->item_type_relation_id = $itr->id;
                    $item_relation->insert();
                    //too expensive??  maybe simply expire atom cache??
                    $item_relation->saveParentAtom();
                    $item_relation->saveChildAtom();
					$r->renderOk('relationship created');
				} else {
					$r->renderError(409,'relationship already exists');
				}
            } else {
                $r->renderError(400);
            }
		} else {
			$r->renderError(415,'cannot accept '.$content_type);
		}
		$r->renderError(400,'something is awry');
	}

	/** this is used to UPDATE an item's type (comes from a form)*/
	public function postToItemType($r)
	{
		$user = $r->getUser();
		if (!$user->can('write',$this->item)) {
			$r->renderError(401,'cannot write to this item');
		}
		if ($this->item->setItemType($r->get('item_type'))) {
			$type = $this->item->getItemType()->name;
			$this->item->expireCaches($r->retrieve('cache'));
			$this->item->saveAtom();
			if (!$type) {
				$type = 'default/none';
			}
			$r->renderResponse("item type is now $type");
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

	public function getMetadataValue($r)
	{
		//$user = $r->getUser();
		$user = $r->getUser('http');
		if (!$user->can('read',$this->item)) {
			$r->renderError(401,'cannot read metadata');
		}
		if (!$r->has('value_id')) {
			$r->renderError(400,'missing identifier');
		}
		$value = new Dase_DBO_Value($this->db);
		$value->load($r->get('value_id'));
		if ($value) {
			$r->renderResponse($value->value_text);
		} else {
			$r->renderError(404);
		}
	}

	public function putMetadataValue($r)
	{
		$value_text = $r->getBody();
		$user = $r->getUser('http');
		if (!$user->can('write',$this->item)) {
			$r->renderError(401,'cannot put metadata');
		}
		if (!$r->has('value_id')) {
			$r->renderError(400,'missing identifier');
		}
		$value_id = $r->get('value_id');
		$this->item->updateMetadata($value_id,htmlspecialchars($value_text),$user->eid);
		$r->renderResponse($value_text);
	}

	public function putTitle($r)
	{
		$title_text = $r->getBody();
		$user = $r->getUser('http');
		if (!$user->can('write',$this->item)) {
			$r->renderError(401,'cannot put title');
		}
		$this->item->updateTitle(htmlspecialchars($title_text),$user->eid);
		$r->renderResponse($title_text);
	}

	public function getTitle($r)
	{
		$user = $r->getUser('http');
		if (!$user->can('read',$this->item)) {
			$r->renderError(401,'cannot get title');
		}
		$r->renderResponse($this->item->getTitle());
	}

	public function deleteMetadataValue($r)
	{
		//$user = $r->getUser();
		$user = $r->getUser('http');
		if (!$user->can('write',$this->item)) {
			$r->renderError(401,'cannot delete metadata');
		}
		if (!$r->has('value_id')) {
			$r->renderError(400,'missing identifier');
		}
		//try/catch??
		$value_id = $r->get('value_id');
		$this->item->removeMetadata($value_id,$user->eid);
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
			$raw_input = $r->getBody();
			$client_md5 = $r->getHeader('Content-MD5');
			//if Content-MD5 header isn't set, we just won't check
			if ($client_md5 && md5($raw_input) != $client_md5) {
				$r->renderError(412,'md5 does not match');
			}
			try {
				$item_entry = Dase_Atom_Entry::load($raw_input,'item');
			} catch(Exception $e) {
				$r->logger()->debug('item handler error: '.$e->getMessage());
				$r->renderError(400,'bad xml');
			}
			if ('item' != $item_entry->entrytype) {
				//$item_entry->setEntryType('item');
				$r->renderError(400,'must be an item entry');
			}
			$item = $item_entry->update($this->db,$r);
			if ($item) {
				$r->renderOk('item has been updated');
			} else {
				$r->renderError(500,'item not updated');
			}
		} else {
			$r->renderError(415,'cannot accept '.$content_type);
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
		if ('0' == $r->get('limit')) {
			$r->renderResponse($this->item->getCommentsJson($r->app_root));
		}
		$r->renderResponse($this->item->getCommentsJson($r->app_root,$user->eid));
	}

	/** this allows us to swap in an item file from the interwebs */
	public function postToIngester($r)
	{
		$user = $r->getUser('http');
		if (!$user->can('write',$this->collection)) {
			$r->renderError(401,'no go unauthorized');
		}
		$content_type = $r->getContentType();
		if ('text/uri-list' == $content_type ) {
			$eid = $r->getUser('http')->eid;
			$url = $r->getBody();
			$filename = array_pop(explode('/',$url));
			$ext = array_pop(explode('.',$url));
			$upload_dir = $this->path_to_media.'/'.$this->collection->ascii_id.'/uploaded_files';
			if (!file_exists($upload_dir)) {
				$r->renderError(401,'missing upload directory');
			}
			$new_file = $upload_dir.'/'.$item->serial_number.'.'.$ext;
			file_put_contents($new_file,file_get_contents($url));
			try {
				$file = Dase_File::newFile($this->db,$new_file,$content_type,null,$r->base_path);
				//since we are swapping in:
				$item->deleteAdminValues();
				//note: this deletes ALL media!!!
				$item->deleteMedia($this->path_to_media);
				$media_file = $file->addToCollection($item,false,$this->path_to_media);  //set 2nd param to true to test for dups
				unlink($new_file);
			} catch(Exception $e) {
				$r->logger()->debug('item handler error: '.$e->getMessage());
				$r->renderError(500,'could not ingest file ('.$e->getMessage().')');
			}
			$item->buildSearchIndex();
			$r->renderOk();
		} else {
			$r->renderError(415,'cannot accept '.$content_type);
		}
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
		$bits = $r->getBody();

		$slug_name = '';
		if ( $r->slug ) {
			$item->setValue('title',$r->slug);
			$slug_name = $r->slug;
		}

		$upload_dir = $this->path_to_media.'/'.$coll->ascii_id.'/uploaded_files';
		if (!file_exists($upload_dir)) {
			$r->renderError(401,'missing upload directory '.$upload_dir);
		}

		$new_file = $upload_dir.'/'.$item->serial_number;

		$ifp = @ fopen( $new_file, 'wb' );
		if (!$ifp) {
			$r->renderError(500,'cannot write file '.$new_file);
		}

		@fwrite( $ifp, $bits );
		fclose( $ifp );
		// Set correct file permissions
		@ chmod( $new_file,0644);

		try {
			$file = Dase_File::newFile($this->db,$new_file,$content_type,$slug_name,$r->base_path);

			//this'll create thumbnail, viewitem, and any derivatives
			//then return the Dase_DBO_MediaFile for the original
			$media_file = $file->addToCollection($item,false,$this->path_to_media);  //set 2nd param to true to test for dups
		} catch(Exception $e) {
			$r->logger()->debug('item handler error: '.$e->getMessage());
			//delete uploaded file
			unlink($new_file);
			$r->renderError(500,'could not ingest media file ('.$e->getMessage().')');
		}
		$item->expireCaches($r->retrieve('cache'));
		$item->buildSearchIndex();
		//delete uploaded file
		unlink($new_file);
		//the returned atom entry links to derivs!
		$mle_url = $r->app_root .'/media/'.$media_file->p_collection_ascii_id.'/'.$media_file->p_serial_number.'.atom';
		header("Location:". $mle_url,TRUE,201);
		$r->response_mime_type = 'application/atom+xml';
		$r->renderResponse($media_file->asAtom($r->app_root));
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
		$r->renderResponse($this->item->getAtompubServiceDoc($r->app_root));
	}
}

