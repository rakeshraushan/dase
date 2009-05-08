<?php

class Dase_Handler_Media extends Dase_Handler
{
	public $resource_map = array(
		'{collection_ascii_id}' => 'collection',
		'{collection_ascii_id}/archive' => 'archive',
		'{collection_ascii_id}/{size}/{serial_number}' => 'media_file',
		'{collection_ascii_id}/{serial_number}' => 'media', //for 'PUT'
	);

	protected function setup($r)
	{
		//I think we could define remote media collections here

		//todo: finish
		//note: this handler (for GETs) needs to be fast
		$this->collection_ascii_id = $r->get('collection_ascii_id');
		$this->serial_number = $r->get('serial_number');
		if ($r->has('size')) {
			$this->size = $r->get('size');
		} 
		if ('get' != $r->method) {
			$this->user = $r->getUser('service');
		}

		/*
		if (!Dase_Acl::check($this->db,$this->collection_ascii_id,$this->size,null,$this->path_to_media)) {
			if (!$path) {
				$this->user = $r->getUser();
				if (!$this->user) {
					$r->renderError(401,'cannot access media');
				}
				if (!Dase_Acl::check($this->db,$this->collection_ascii_id,$this->size,$this->user->eid,$this->path_to_media)) {
					$r->renderError(401,'cannot access media');
				}
			}
			//get coll path to media!!!!!!!!
		}
		 */
	}

	public function getMediaFileTxt($r)
	{
		$r->serveFile($this->_getFilePath($this->path_to_media,$this->collection_ascii_id,$this->serial_number,$this->size,$r->format),$r->response_mime_type);
	}

	public function getMediaFileJpg($r)
	{
		$r->serveFile($this->_getFilePath($this->path_to_media,$this->collection_ascii_id,$this->serial_number,$this->size,$r->format),$r->response_mime_type);
	}

	public function getMediaFilePdf($r)
	{
		$r->serveFile($this->_getFilePath($this->path_to_media,$this->collection_ascii_id,$this->serial_number,$this->size,$r->format),$r->response_mime_type);
	}

	public function getMediaFileGif($r)
	{
		$r->serveFile($this->_getFilePath($this->path_to_media,$this->collection_ascii_id,$this->serial_number,$this->size,$r->format),$r->response_mime_type);
	}

	public function getMediaFilePng($r)
	{
		$r->serveFile($this->_getFilePath($this->path_to_media,$this->collection_ascii_id,$this->serial_number,$this->size,$r->format),$r->response_mime_type);
	}

	public function getMediaFileMp3($r)
	{
		$r->serveFile($this->_getFilePath($this->path_to_media,$this->collection_ascii_id,$this->serial_number,$this->size,$r->format),$r->response_mime_type);
	}

	public function getMediaFileMov($r)
	{
		$r->serveFile($this->_getFilePath($this->path_to_media,$this->collection_ascii_id,$this->serial_number,$this->size,$r->format),$r->response_mime_type);
	}

	/** for html files */
	public function getMediaFile($r)
	{
		$r->serveFile($this->_getFilePath($this->path_to_media,$this->collection_ascii_id,$this->serial_number,$this->size,$r->format),$r->response_mime_type);
	}

	public function getMediaFileCss($r)
	{
		$r->serveFile($this->_getFilePath($this->path_to_media,$this->collection_ascii_id,$this->serial_number,$this->size,$r->format),$r->response_mime_type);
	}

	/** used to ADD a media file to an item */
	public function putMediaFile($r)
	{
		//todo: not sure if this is needed -- add one size to an item
	}

	/** used for swap-out */
	public function putMedia($r)
	{
		$item = Dase_DBO_Item::get($this->db,$this->collection_ascii_id,$this->serial_number);
		if (!$item) {
			$r->renderError(404,'no such item');
		}
		if (!$this->user->can('write',$item)) {
			$r->renderError(401,'cannot put media to this item');
		}
		$coll = $item->getCollection();
		if(!isset($_SERVER['CONTENT_LENGTH']) || !isset($_SERVER['CONTENT_TYPE'])) {
			$r->renderError(411,'missing content length');
		}

		$content_type = Dase_Media::isAcceptable($r->getContentType());
		if (!$content_type) {
			$r->renderError(415,'not an accepted media type');
		}

		$bits = $r->getBody();
		$upload_dir = $this->path_to_media.'/'.$coll->ascii_id.'/uploaded_files';
		if (!file_exists($upload_dir)) {
			$r->renderError(401,'missing upload directory '.$upload_dir);
		}

		$new_file = $upload_dir.'/'.$item->serial_number;

		$ifp = @ fopen( $new_file, 'wb' );
		if (!$ifp) {
			$r->renderError(500);
		}

		@fwrite( $ifp, $bits );
		fclose( $ifp );
		// Set correct file permissions
		@ chmod( $new_file,0644);

		try {
			$file = Dase_File::newFile($this->db,$new_file,$content_type,null,$r->base_path);
			//since we are swapping in:
			$item->deleteAdminValues();
			//note: this deletes ALL media!!!
			$item->deleteMedia($this->path_to_media);
			$media_file = $file->addToCollection($item,false,$this->path_to_media);  //set 2nd param to true to test for dups
			unlink($new_file);
		} catch(Exception $e) {
			$r->logger()->debug('media handler error: '.$e->getMessage());
			$r->renderError(500,'could not ingest file ('.$e->getMessage().')');
		}
		$item->buildSearchIndex();
		$r->renderOk();
	}

	/** GET on edit-media url */
	public function getMedia($r)
	{
		$item = Dase_DBO_Item::get($this->db,$this->collection_ascii_id,$this->serial_number);
		if (!$item) {
			$r->renderError(404,'no such item');
		}
		$m = $item->getEnclosure();
		if ($m) {
			$format = Dase_File::$types_map[$m->mime_type]['ext'];
			$r->serveFile($m->getLocalPath($this->path_to_media),$m->mime_type);
		} else {
			$r->renderError(404);
		}
	}

	/** AtomPub Media Link Entry */
	public function getMediaAtom($r)
	{
		$item = Dase_DBO_Item::get($this->db,$this->collection_ascii_id,$this->serial_number);
		if (!$item) {
			$r->renderError(404,'no such item');
		}
		$m = $item->getEnclosure();
		if ($m) {
			$r->renderResponse($m->asAtom($r->app_root));
		} else {
			$r->renderError(404,'no enclosure');
		}
	}

	public function deleteMedia($r)
	{
		$item = Dase_DBO_Item::get($this->db,$this->collection_ascii_id,$this->serial_number);
		if (!$item) {
			$r->renderError(404,'no such item');
		}
		if (!$this->user->can('write',$item)) {
			$r->renderError(401,'cannot delete media in this item');
		}
		try {
			$item->deleteAdminValues();
			//move actual files to 'deleted' directory
			$item->deleteMedia($this->path_to_media);
		} catch(Exception $e) {
			$r->logger()->debug('media handler error: '.$e->getMessage());
			$r->renderError(500,'could not delete media ('.$e->getMessage().')');
		}
		$item->buildSearchIndex();
		$r->renderOk('deleted resource');
	}

	private function _fixSizeExt($serial_number,$size)
	{
		switch ($size) {
		case 'thumbnail':
			if ('_100' == substr($serial_number,-4)) {
				return substr($serial_number,0,-4);
			}
		case 'viewitem':
			if ('_400' == substr($serial_number,-4)) {
				return substr($serial_number,0,-4);
			}
		case 'small':
			if ('_640' == substr($serial_number,-4)) {
				return substr($serial_number,0,-4);
			}
		case 'medium':
			if ('_800' == substr($serial_number,-4)) {
				return substr($serial_number,0,-4);
			}
		case 'large':
			if ('_1024' == substr($serial_number,-5)) {
				return substr($serial_number,0,-5);
			}
		case 'full':
			if ('_2700' == substr($serial_number,-5)) {
				return substr($serial_number,0,-5);
			}
			if ('_3600' == substr($serial_number,-5)) {
				return substr($serial_number,0,-5);
			}
		}
		return $serial_number;
	}

	private function _getFilePath($path_to_media,$collection_ascii_id,$serial_number,$size,$format)
	{
		//look first in subdir
		//get serial number w/o size extension
		$subdir = Dase_Util::getSubdir($this->_fixSizeExt($serial_number,$size));
		$path = $path_to_media.'/'.
			$collection_ascii_id.'/'.
			$size.'/'.
			$subdir.'/'.
			$serial_number.'.'.$format;
		if (file_exists($path)) {
			return $path;
		} else {
			$path = $path_to_media.'/'.
				$collection_ascii_id.'/'.
				$size.'/'.
				$serial_number.'.'.$format;
			return $path;
		}
	}

	/** this is simply a GET of the same URI we post media to */
	public function getCollectionAtom($r) 
	{
		$c = Dase_DBO_Collection::get($this->db,$this->collection_ascii_id);
		if ($r->has('limit')) {
		   $limit = $r->get('limit');
		} else {
			$limit = 20;
		}
		$r->renderResponse($c->asAtom($r->app_root,$limit));
	}

	/** this simply allows us to see if a media archive exists
	 * even if the corresponding collection does not
	 * */
	public function getArchive($r) 
	{
		$media_dir =  $r->retrieve('config')->getMediaDir().'/'.$this->collection_ascii_id;
		if (file_exists($media_dir)) {
			//todo: think about this...
			$r->renderOk('media archive exists');
		} else {
			$r->renderError(404,'media archive does not exist');
		}
	}

	public function postToCollection($r)
	{
		$c = Dase_DBO_Collection::get($this->db,$r->get('collection_ascii_id'));
		if (!$this->user->can('write',$c)) {
			$r->renderError(401,'cannot post media to this collection');
		}
		//hand off to item handler
		try {
			$item_handler = new Dase_Handler_Item($this->db,$r->retrieve('config'));
			//allows us to dictate serial number
			$sernum = Dase_Util::makeSerialNumber($r->slug);
			$item_handler->item = $c->createNewItem($sernum,$this->user->eid);
			$item_handler->postToMedia($r);
		} catch (Exception $e) {
			$r->renderError(409,$e->getMessage());
		}
		//if something goes wrong and control returns here
		$r->renderError(500,'error in post to collection');
	}
}

