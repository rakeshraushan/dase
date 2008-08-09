<?php

class Dase_Handler_Media extends Dase_Handler
{
	public $resource_map = array(
		'{collection_ascii_id}' => 'media_collection',
		'{collection_ascii_id}/{size}/{serial_number}' => 'media_file',
	);

	protected function setup($request)
	{
		//finish!!!!!!!!!!!!!!!!!!!!!!!!!!
		$this->collection_ascii_id = $request->get('collection_ascii_id');
		$this->serial_number = $request->get('serial_number');
		$this->size = $request->get('size');
		/*
		if (!Dase_Acl::check($this->collection_ascii_id,$this->size)) {
			if (!$path) {
				$user = $request->getUser();
				if (!$user) {
					$request->renderError(401,'cannot access media');
				}
				if (!Dase_Acl::check($this->collection_ascii_id,$this->size,$user->eid)) {
					$request->renderError(401,'cannot access media');
				}
			}
			//get coll path to media!!!!!!!!
		}
		 */
	}

	public function getMediaFileJpg($request)
	{
		$request->serveFile($this->_getFilePath($this->collection_ascii_id,$this->serial_number,$this->size,$request->format),$request->response_mime_type);
	}

	/** AtomPub Media Link Entry */
	public function getMediaFileAtom($request)
	{
		$collection_ascii_id = $request->get('collection_ascii_id');
		$serial_number = $request->get('serial_number');
		$size = $request->get('size');
		$m = new Dase_DBO_MediaFile;
		$m->p_collection_ascii_id = $collection_ascii_id;
		$m->p_serial_number = $serial_number;
		$m->size = $size; //meaning media directory
		if ($m->findOne()) {
			$mle_url = APP_ROOT .'/media/'.$m->p_collection_ascii_id.'/'.$m->size.'/'.$m->p_serial_number.'.atom';
			header("Location:". $mle_url,TRUE,201);
			$request->response_mime_type = 'application/atom+xml';
			$request->renderResponse($m->asAtom());
		}
	}

	public function postToMediaCollection($request) 
	{
		$this->user = $request->getUser('http');
		$c = Dase_DBO_Collection::get($this->collection_ascii_id);
		if (!$this->user->can('write','collection',$c)) {
			$request->renderError(401,'user cannot post media to collection');
		}
		//acceptable mime types
		$types = Dase_Config::get('media_types');
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

		$slug = '';
		if ( isset( $_SERVER['HTTP_SLUG'] ) ) {
			$slug = Dase_Util::dirify( $_SERVER['HTTP_SLUG'] );
		} elseif ( isset( $_SERVER['HTTP_TITLE'] ) ) {
			$slug = Dase_Util::dirify( $_SERVER['HTTP_TITLE'] );
		} else {
			$slug = $item->serial_number;
		}
		$upload_dir = Dase_Config:get('path_to_media').'/'.$c->ascii_id.'/uploaded_files';
		if (!file_exists($upload_dir)) {
			$request->renderError(401,'missing upload directory');
		}

		$ext = $subtype;
		$new_file = $upload_dir.'/'.$item->serial_number.'.'.$ext;

		$ifp = @ fopen( $new_file, 'wb' );
		if (!$ifp) {
			Dase::error(500);
		}

		@fwrite( $ifp, $bits );
		fclose( $ifp );
		// Set correct file permissions
		@ chmod( $new_file,0644);

		//NOW do a 'file upload' a la DASe
		try {
			//$u = new Dase_Upload(Dase_File::newFile($new_file),$item->getCollection(),false); //false means do NOT check for dup
			$u = new Dase_Upload(Dase_File::newFile($new_file),$item->getCollection(),true); //false means do NOT check for dup
			//may need to account for multi-tiff
			//$u->checkForMultiTiff();
			$u->setItem($item);
			$u->ingest();
			$u->setTitle($slug);
			$u->buildSearchIndex();
		} catch(Exception $e) {
			Dase_Log::debug('error',$e->getMessage());
			$request->renderError(500,'could not ingest file');
		}
		$m = new Dase_DBO_MediaFile;
		$m->p_collection_ascii_id = $coll->ascii_id;
		$m->p_serial_number = $item->serial_number;
		$m->size = $u->getDaseFileSize(); //meaning media directory
		if ($m->findOne()) {
			$mle_url = APP_ROOT .'/media/'.$m->p_collection_ascii_id.'/'.$m->size.'/'.$m->p_serial_number.'.atom';
			header("Location:". $mle_url,TRUE,201);
			$request->response_mime_type = 'application/atom+xml';
			$request->renderResponse($m->asAtom());
		}
	}

	private function _getFilePath($collection_ascii_id,$serial_number,$size,$format)
	{
		$sizes = array(
			'thumbnail' => array( 'dir' => 'thumbnails'),
			'viewitem' => array( 'dir' => '400'),
			'small' => array( 'dir' => 'small'),
			'medium' => array( 'dir' => 'medium'),
			'large' => array( 'dir' => 'large'),
			'full' => array( 'dir' => 'full'),
			'jpg' => array( 'dir' => 'jpeg'),
			'jpeg' => array( 'dir' => 'jpeg'),
		);
		$path = Dase_Config::get('path_to_media').'/'.
			$collection_ascii_id.'/'.
			$sizes[$size]['dir'].'/'.
			$serial_number.'.'.$format;
		return $path;
	}
}

