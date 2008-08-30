<?php

class Dase_Handler_Media extends Dase_Handler
{
	public $resource_map = array(
		'{collection_ascii_id}' => 'collection',
		'{collection_ascii_id}/{size}/{serial_number}' => 'media_file',
		'{collection_ascii_id}/{serial_number}' => 'media_file', //for 'PUT'
	);

	protected function setup($r)
	{
		//finish!!!!!!!!!!!!!!!!!!!!!!!!!!
		$this->collection_ascii_id = $r->get('collection_ascii_id');
		$this->serial_number = $r->get('serial_number');
		if ($r->has('size')) {
			$this->size = $r->get('size');
		} else {
			if ('put' != $r->method) {
				$r->renderError(404);
			}
		}


		/*
		if (!Dase_Acl::check($this->collection_ascii_id,$this->size)) {
			if (!$path) {
				$user = $r->getUser();
				if (!$user) {
					$r->renderError(401,'cannot access media');
				}
				if (!Dase_Acl::check($this->collection_ascii_id,$this->size,$user->eid)) {
					$r->renderError(401,'cannot access media');
				}
			}
			//get coll path to media!!!!!!!!
		}
		 */
	}

	public function getMediaFileJpg($r)
	{
		$r->serveFile($this->_getFilePath($this->collection_ascii_id,$this->serial_number,$this->size,$r->format),$r->response_mime_type);
	}

	/** used for swap-out */
	public function putMediaFile($r)
	{
		$item = Dase_DBO_Item::get($this->collection_ascii_id,$this->serial_number);
		if (!$item) {
			$r->renderError(404,'no such item');
		}
		$user = $r->getUser('http');
		if (!$user->can('write',$item)) {
			$r->renderError(401,'cannot put media to this item');
		}
		$coll = $item->getCollection();
		$types = array('image/*','audio/*','video/*','application/pdf');
		if(!isset($_SERVER['CONTENT_LENGTH']) || !isset($_SERVER['CONTENT_TYPE'])) {
			$r->renderError(411,'missing content length');
		}
		//clean this up (prob from wordpress)
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
			//since we are swapping in:
			$item->deleteAdminvalues();
			//note: this deletes ALL media!!!
			$item->deleteMedia();
			$media_file = $file->addToCollection($item,false);  //set 2nd param to true to test for dups
		} catch(Exception $e) {
			Dase_Log::debug('error',$e->getMessage());
			$r->renderError(500,'could not ingest file ('.$e->getMessage().')');
		}
		$item->buildSearchIndex();
		//the returned atom entry links to derivs!
		$mle_url = APP_ROOT .'/media/'.$media_file->p_collection_ascii_id.'/'.$media_file->size.'/'.$media_file->p_serial_number.'.atom';
		header("Location:". $mle_url,TRUE,201);
		$r->response_mime_type = 'application/atom+xml';
		$r->renderResponse($media_file->asAtom());
	}

	/** AtomPub Media Link Entry */
	public function getMediaFileAtom($r)
	{
		$collection_ascii_id = $r->get('collection_ascii_id');
		$serial_number = $r->get('serial_number');
		$size = $r->get('size');
		$m = new Dase_DBO_MediaFile;
		$m->p_collection_ascii_id = $collection_ascii_id;
		$m->p_serial_number = $serial_number;
		$m->size = $size; //meaning media directory
		if ($m->findOne()) {
			$mle_url = APP_ROOT .'/media/'.$m->p_collection_ascii_id.'/'.$m->size.'/'.$m->p_serial_number.'.atom';
			header("Location:". $mle_url,TRUE,201);
			$r->response_mime_type = 'application/atom+xml';
			$r->renderResponse($m->asAtom());
		}
	}

	private function _getFilePath($collection_ascii_id,$serial_number,$size,$format)
	{
		$path = Dase_Config::get('path_to_media').'/'.
			$collection_ascii_id.'/'.
			$size.'/'.
			$serial_number.'.'.$format;
		return $path;
	}

	public function postToCollection($r)
	{
		$user = $r->getUser('http');
		$c = Dase_DBO_Collection::get($r->get('collection_ascii_id'));
		if (!$user->can('write',$c)) {
			$r->renderError(401,'cannot post media to this item');
		}
		//hand off to item handler
		$item_handler = new Dase_Handler_Item;
		$item_handler->item = $c->createNewItem(null,$user->eid);
		$item_handler->postToMedia($r);
		//if something goes wrong and control returns here
		$r->renderError(500,'error in post to collection');
	}
}

