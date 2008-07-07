<?php

class Dase_Handler_Item extends Dase_Handler
{
	public $resource_map = array( 
		'{collection_ascii_id}/{serial_number}' => 'item',
		'{collection_ascii_id}/{serial_number}/edit' => 'edit_form',
		'{collection_ascii_id}/{serial_number}/notes' => 'notes',
		'{collection_ascii_id}/{serial_number}/service' => 'service',
		'{collection_ascii_id}/{serial_number}/notes/{note_id}' => 'note',
	);

	protected function setup($request)
	{
		$this->item = Dase_DBO_Item::get($request->get('collection_ascii_id'),$request->get('serial_number'));
		if (!$this->item) {
			$request->renderError(404);
		}

		//todo: work on better auth mapping
		if ('service' == $request->resource || 'atom' == $request->format) {
			$this->user = $request->getUser('http');
		} else {
			$this->user = $request->getUser();
			if (!$this->user->can('read','item',$this->item)) {
				$request->renderError(401);
			}
		}
	}	

	public function getItemAtom($request)
	{
		$request->renderResponse($this->item->asAtom());
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
		$t->assign('item',$feed);
		$request->renderResponse($t->fetch('item/transform.tpl'));
	}

	public function getEditFormJson($request)
	{
		$request->renderResponse($this->item->getEditFormJson());
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

	public function getNotesJson($request)
	{
		$request->renderResponse($this->item->getContentsJson());
	}

	/** from last version: work on this!!!!!!!!! */
	public function postToItem($request) 
	{
		$this->user = $request->getUser('http');
		if (!$this->user->can('read','item',$this->item)) {
			$request->renderError(401);
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

		$coll = $this->item->getCollection();

		$slug = '';
		if ( isset( $_SERVER['HTTP_SLUG'] ) ) {
			$slug = Dase_Util::dirify( $_SERVER['HTTP_SLUG'] );
		} elseif ( isset( $_SERVER['HTTP_TITLE'] ) ) {
			$slug = Dase_Util::dirify( $_SERVER['HTTP_TITLE'] );
		} else {
			$slug = $item->serial_number;
		}
		$upload_dir = $coll->path_to_media_files.'/uploaded_files';
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
			Dase::log('error',$e->getMessage());
			Dase::error(500);
		}
		$m = new Dase_DBO_MediaFile;
		$m->p_collection_ascii_id = $coll->ascii_id;
		$m->p_serial_number = $this->item->serial_number;
		$m->size = $u->getDaseFileSize(); //meaning media directory
		if ($m->findOne()) {
			$mle_url = APP_ROOT .'/media/'.$m->p_collection_ascii_id.'/'.$m->size.'/'.$m->p_serial_number.'.atom';
			header("Location:". $mle_url,TRUE,201);
			$request->response_mime_type = 'application/atom+xml';
			$request->renderResponse($m->asAtom());
		}
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

