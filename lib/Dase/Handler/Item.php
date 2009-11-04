<?php

class Dase_Handler_Item extends Dase_Handler
{
	public $resource_map = array( 
		'{collection_ascii_id}/{serial_number}' => 'item',
		//includes authorized urls on media
		'{collection_ascii_id}/{serial_number}/authorized' => 'authorized',
		'{collection_ascii_id}/{serial_number}/indexer' => 'indexer',
		'{collection_ascii_id}/{serial_number}/ingester' => 'ingester',
		'{collection_ascii_id}/{serial_number}/input_template' => 'input_template',
		'{collection_ascii_id}/{serial_number}/ping' => 'ping',
		'{collection_ascii_id}/{serial_number}/media' => 'media',
		'{collection_ascii_id}/{serial_number}/media/count' => 'media_count',
		//used for get and post
		//also atom categories PUT
		'{collection_ascii_id}/{serial_number}/metadata' => 'metadata',
		//used for put and delete
		'{collection_ascii_id}/{serial_number}/metadata/title' => 'title',
		'{collection_ascii_id}/{serial_number}/metadata/links' => 'metadata_links',
		'{collection_ascii_id}/{serial_number}/metadata/{value_id}' => 'metadata_value',
		'{collection_ascii_id}/{serial_number}/comments' => 'comments',
		'{collection_ascii_id}/{serial_number}/content' => 'content',
		'{collection_ascii_id}/{serial_number}/service' => 'service',
		'{collection_ascii_id}/{serial_number}/solr' => 'solr',
		'{collection_ascii_id}/{serial_number}/solr_response' => 'solr_response',
		'{collection_ascii_id}/{serial_number}/status' => 'status',
		'{collection_ascii_id}/{serial_number}/item_type' => 'item_type',
		'{collection_ascii_id}/{serial_number}/tags' => 'tags',
		'{collection_ascii_id}/{serial_number}/comments/{comment_id}' => 'comment',
	);

	protected function setup($r)
	{
		//do we really want to hit db w/ every request?
		$this->item = Dase_DBO_Item::get($this->db,$r->get('collection_ascii_id'),$r->get('serial_number'));
		if (!$this->item && 'put' != $r->method) {
			$r->renderError(404);
		}

		//all auth happens in individual methods
	}	

	public function getInputTemplate($r) 
	{
		$t = new Dase_Template($r);
		$type = $this->item->getItemType();
		$t->assign('item_url','item/'.$r->get('collection_ascii_id').'/'.$r->get('serial_number'));
		$t->assign('atts',$type->getAttributes());
		$r->renderResponse($t->fetch('item/input_template.tpl'));

	}

	public function deleteItem($r)
	{
		$user = $r->getUser('service');
		if (!$user->can('write',$this->item)) {
			$r->renderError(401,'user cannot delete this item');
		}
		try {
			$this->item->expunge(MEDIA_DIR);
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

	public function getAuthorizedAtom($r)
	{
		$user = $r->getUser('http');
		if (!$user->can('read',$this->item)) {
			$r->renderError(401,'user cannot read this item');
		}
		if ('feed' == $r->get('type')) {
			$r->renderResponse($this->item->asAtom($r->app_root,$r->token));
		} else {
			$r->renderResponse($this->item->asAtomEntry($r->app_root,$r->token));
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
		//json in solr is really for json search results
		//use atom json for single items
		//$r->renderResponse($this->item->asJson($r->app_root));
		$r->renderResponse($this->item->asAtomJson($r->app_root));
	}

	public function getItemAtom($r)
	{
		$user = $r->getUser('http');
		if (!$user->can('read',$this->item)) {
			$r->renderError(401,'user cannot read this item');
		}
		if ('feed' == $r->get('type')) {
			$r->renderResponse($this->item->asAtom($r->app_root));
		} else {
			$r->renderResponse($this->item->asAtomEntry($r->app_root));
		}
	}

	public function headItem($r) 
	{
		exit();
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
			$r->app_root.'/item/'. 
			$r->get('collection_ascii_id') . '/' . 
			$r->get('serial_number').'.atom?type=feed',
				$user->eid,$user->getHttpPassword());

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
			$meta = $this->item->getMetadata($r->app_root,$r->get('display'));
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

	/** displays the doc as POSTed to Solr */
	public function getSolrXml($r)
	{
		$user = $r->getUser();
		if (!$user->can('read',$this->item)) {
			$r->renderError(401,'user cannot read this item');
		}
		$solr = Dase_SearchEngine::get($this->db,$this->config);
		$r->renderResponse($solr->getItemSolrDoc($this->item));
	}

	public function getSolrResponseXml($r)
	{
		$user = $r->getUser();
		if (!$user->can('read',$this->item)) {
			$r->renderError(401,'user cannot read this item');
		}
		$ds = Dase_DocStore::get($this->db,$this->config);
		$r->renderResponse($ds->getSolrResponse($this->item->getUnique()));
	}

	/* displays Atom doc FROM Solr */
	public function getSolrAtom($r)
	{
		$user = $r->getUser();
		if (!$user->can('read',$this->item)) {
			$r->renderError(401,'user cannot read this item');
		}
		$r->checkCache();
		if ('entry' == $r->get('format')) {
			$as_feed = false;
		} else {
			$as_feed = true;
		}
		$ds = Dase_DocStore::get($this->db,$this->config);
		$r->renderResponse($ds->getItem($this->item->getUnique(),$r->app_root,$as_feed));
	}

	public function postToComments($r)
	{
		$user = $r->getUser();
		if (!$user->can('read',$this->item)) {
			$r->renderError(401,'cannot read this item');
		}
		//auth: anyone can post to an item they can read
		$bits = trim(strip_tags($r->getBody()));
		
		if ($bits) {
			$this->item->addComment($bits,$user->eid);
			//comments should NOT be globally searchable
			//$this->item->buildSearchIndex();
			$r->renderResponse('added comment: '.$bits);
		} else {
			$r->renderResponse('no comment');
		}
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

	//for input template
	public function postToItem($r)
	{
		$user = $r->getUser();
		if (!$user->can('write',$this->item)) {
			$r->renderError(401,'cannot write to this item');
		}
		$content_type = $r->getContentType();
		if ('application/x-www-form-urlencoded' != $content_type) {
			$r->renderError(401);
		} 
		$type = $this->item->getItemType();
		$set = '';
		foreach ($type->getAttributes() as $att) {
			$val_set = $r->get($att->ascii_id,true);
			if (count($r->get($att->ascii_id,true)) && is_array($val_set)) {
				foreach ($val_set as $val) {
					if ($att->ascii_id && $val) {
						$this->item->setValue($att->ascii_id,$val);
					}
				}
			}
		}
		$this->item->buildSearchIndex();
		$r->renderRedirect('item/'.$r->get('collection_ascii_id').'/'.$r->get('serial_number'));
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
			$this->item->expireCaches($r->getCache());
			$this->item->buildSearchIndex();
			if (!$type) {
				$type = 'default/none';
			}
			$r->renderResponse("item type is now $type");
		}
	}

	public function postToMetadata($r)
	{
		$content_type = $r->getContentType();
		if ('application/x-www-form-urlencoded' == $content_type) {
			$user = $r->getUser();
			if (!$user->can('write',$this->item)) {
				$r->renderError(401,'cannot post to metadata');
			}
			$att_ascii = $r->get('ascii_id');
			foreach ($r->get('value',true) as $val) {
				if ($val) {
					$this->item->setValue($att_ascii,$val);
				}
			}
			$this->item->buildSearchIndex();
			$r->renderResponse('added metadata (unless null)');
		}
		//json should be simple object keyvals
		if ('application/json' == $content_type) {
			$user = $r->getUser('http');
			if (!$user->can('write',$this->item)) {
				$r->renderError(401,'cannot post to metadata');
			}
			$json = $r->getBody();
			$metadata_array = Dase_Json::toPhp($json);
			foreach ($metadata_array as $att => $val) {
				if (is_array($val)) {
					foreach ($val as $v) {
						$this->item->setValue($att,$v);
					}
				} else {
					$this->item->setValue($att,$val);
				}
			}
			$this->item->buildSearchIndex();
			$r->renderResponse('added json metadata (unless null)');
		}
		$r->renderError(415,'cannot accept '.$content_type);
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

	/** for adding metadata link(s) */
	public function postToMetadataLinks($r)
	{
		$user = $r->getUser('http');
		if ($this->item && !$user->can('write',$this->item)) {
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
				Dase_Log::debug(LOG_FILE,'item handler error: '.$e->getMessage());
				$r->renderError(400,'bad xml');
			}
			if ('item' != $item_entry->entrytype) {
				//$item_entry->setEntryType('item');
				$r->renderError(400,'must be an item entry');
			}
			$item = $item_entry->addLinks($this->db,$r);
			if ($item) {
				$r->renderOk('item has been updated');
			} else {
				$r->renderError(500,'item not updated');
			}
		} else {
			$r->renderError(415,'cannot accept '.$content_type);
		}
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
		if ($this->item && !$user->can('write',$this->item)) {
			$r->renderError(401,'cannot update item');
		}
		if (!$this->item) {
			$collection = Dase_DBO_Collection::get($this->db,$r->get('collection_ascii_id'));
			if (!$user->can('write',$collection)) {
				$r->renderError(401,'cannot update collection');
			}
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
				Dase_Log::debug(LOG_FILE,'item handler error: '.$e->getMessage());
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
		if ('0' == $r->get('all')) {
			$r->renderResponse($this->item->getCommentsJson($r->app_root,$user->eid));
		}
		$r->renderResponse($this->item->getCommentsJson($r->app_root));
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
			$upload_dir = MEDIA_DIR.'/'.$this->collection->ascii_id.'/uploaded_files';
			if (!file_exists($upload_dir)) {
				$r->renderError(500,'missing upload directory');
			}
			$new_file = $upload_dir.'/'.$item->serial_number.'.'.$ext;
			file_put_contents($new_file,file_get_contents($url));
			try {
				$file = Dase_File::newFile($this->db,$new_file,$content_type,null,BASE_PATH);
				//since we are swapping in:
				$item->deleteAdminValues();
				//note: this deletes ALL media!!!
				$item->deleteMedia(MEDIA_DIR);
				$media_file = $file->addToCollection($item,false,MEDIA_DIR);  //set 2nd param to true to test for dups
				unlink($new_file);
			} catch(Exception $e) {
				Dase_Log::debug(LOG_FILE,'item handler error: '.$e->getMessage());
				$r->renderError(500,'could not ingest file ('.$e->getMessage().')');
			}
			$item->buildSearchIndex();
			$r->renderOk();
		} else {
			$r->renderError(415,'cannot accept '.$content_type);
		}
	}

	/** like media->putMedia but this does NOT
	 * delete existing media files
	 */
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

		$orig_name = '';
		if ( $r->http_title ) {
			$item->setValue('title',$r->http_title);
			$orig_name = $r->http_title;
		}
		elseif ( $r->slug ) {
			$item->setValue('title',$r->slug);
			$orig_name = $r->slug;
		}

		$upload_dir = MEDIA_DIR.'/'.$coll->ascii_id.'/uploaded_files';
		if (!file_exists($upload_dir)) {
			Dase_Log::debug(LOG_FILE,'missing upload directory '.$upload_dir);
			$r->renderError(500,'missing upload directory '.$upload_dir);
		}

		$new_file = $upload_dir.'/'.$item->serial_number;

		$ifp = @ fopen( $new_file, 'wb' );
		if (!$ifp) {
			Dase_Log::debug(LOG_FILE,'cannot write file '.$new_file);
			$r->renderError(500,'cannot write file '.$new_file);
		}

		@fwrite( $ifp, $bits );
		fclose( $ifp );
		// Set correct file permissions
		@ chmod( $new_file,0644);

		try {
			$file = Dase_File::newFile($this->db,$new_file,$content_type,$orig_name,BASE_PATH);

			//this'll create thumbnail, viewitem, and any derivatives
			//then return the Dase_DBO_MediaFile for the original
			$media_file = $file->addToCollection($item,false,MEDIA_DIR);  //set 2nd param to true to test for dups
		} catch(Exception $e) {
			Dase_Log::debug(LOG_FILE,'item handler error: '.$e->getMessage());
			//delete uploaded file
			unlink($new_file);
			$r->renderError(500,'could not ingest media file ('.$e->getMessage().')');
		}
		$item->expireCaches($r->getCache());

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

	public function postToIndexer($r)
	{
		$user = $r->getUser('http');
		if (!$user->can('write',$this->item)) {
			$r->renderError(401,'cannot index this item');
		}

		//force indexing & commit
		$resp = $this->item->buildSearchIndex();

		//should use HTTP status code instead
		if ('ok' == substr($resp,0,2)) {
			$r->renderOk('indexed item');
		} else {
			Dase_Log::debug(LOG_FILE,'indexer error: '.$resp);
			$r->renderError(500);
		}
	}

	public function getIndexer($r)
	{
		$r->renderError(405,'POST method expected');
	}
}

