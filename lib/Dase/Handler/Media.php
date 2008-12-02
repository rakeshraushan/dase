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
		if (!Dase_Acl::check($this->collection_ascii_id,$this->size)) {
			if (!$path) {
				$this->user = $r->getUser();
				if (!$this->user) {
					$r->renderError(401,'cannot access media');
				}
				if (!Dase_Acl::check($this->collection_ascii_id,$this->size,$this->user->eid)) {
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

	public function getMediaFilePdf($r)
	{
		$r->serveFile($this->_getFilePath($this->collection_ascii_id,$this->serial_number,$this->size,$r->format),$r->response_mime_type);
	}

	public function getMediaFileGif($r)
	{
		$r->serveFile($this->_getFilePath($this->collection_ascii_id,$this->serial_number,$this->size,$r->format),$r->response_mime_type);
	}

	public function getMediaFileMp3($r)
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
			$file = Dase_File::newFile($new_file,$content_type);
			//since we are swapping in:
			$item->deleteAdminValues();
			//note: this deletes ALL media!!!
			$item->deleteMedia();
			$media_file = $file->addToCollection($item,false);  //set 2nd param to true to test for dups
			unlink($new_file);
		} catch(Exception $e) {
			Dase_Log::debug('error',$e->getMessage());
			$r->renderError(500,'could not ingest file ('.$e->getMessage().')');
		}
		$item->buildSearchIndex();
		$r->renderOk();
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
			$r->renderResponse($m->asAtom());
		} else {
			$r->renderError(401);
		}
	}

	public function deleteMediaFile($r)
	{
		$c = Dase_DBO_Collection::get($r->get('collection_ascii_id'));
		if (!$this->user->can('write',$c)) {
			$r->renderError(401,'cannot delete media in this collection');
		}
		$mf = new Dase_DBO_MediaFile;
		if ($this->size && $this->collection_ascii_id && $this->serial_number) {
			$mf->size = $this->size;
			$mf->p_collection_ascii_id = $this->collection_ascii_id;
			$mf->p_serial_number = $this->serial_number;
			if ($mf->findOne()) {
				$mf->delete();
				$r->renderOk('deleted resource');
			} else {
				$r->renderError(401);
			}
		} else {
			$r->renderError(400,'something missing');
		}
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

	private function _getFilePath($collection_ascii_id,$serial_number,$size,$format)
	{
		//look first in subdir
		$subdir = Dase_Util::getSubdir($this->_fixSizeExt($serial_number,$size));
		$path = Dase_Config::get('path_to_media').'/'.
			$collection_ascii_id.'/'.
			$size.'/'.
			$subdir.'/'.
			$serial_number.'.'.$format;
		if (file_exists($path)) {
			return $path;
		} else {
			$path = Dase_Config::get('path_to_media').'/'.
				$collection_ascii_id.'/'.
				$size.'/'.
				$serial_number.'.'.$format;
			return $path;
		}
	}

	public function getCollectionAtom($r) 
	{
		$c = Dase_DBO_Collection::get($this->collection_ascii_id);
		if ($r->has('limit')) {
		   $limit = $r->get('limit');
		} else {
			$limit = 20;
		}
		$r->renderResponse($c->mediaAsAtom($limit));
	}

	public function postToCollection($r)
	{
		$c = Dase_DBO_Collection::get($r->get('collection_ascii_id'));
		if (!$this->user->can('write',$c)) {
			$r->renderError(401,'cannot post media to this collection');
		}
		//hand off to item handler
		try {
			$item_handler = new Dase_Handler_Item;
			$item_handler->item = $c->createNewItem(null,$this->user->eid);
			$item_handler->postToMedia($r);
		} catch (Exception $e) {
			$r->renderError(500,$e->getMessage());
		}
		//if something goes wrong and control returns here
		$r->renderError(500,'error in post to collection');
	}
}

