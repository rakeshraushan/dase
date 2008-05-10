<?php

class AtompubHandler
{
	public static function getMediaLinkEntry($params) 
	{
		Dase_Auth::authorize('read',$params);
		if (isset($params['collection_ascii_id']) && ($params['serial_number'] && $params['size'])) {
			$media_file = new Dase_DBO_MediaFile;
			$media_file->p_collection_ascii_id = $params['collection_ascii_id'];
			$media_file->p_serial_number = $params['serial_number'];
			$media_file->size = $params['size'];
			if ($media_file->findOne()) {
				Dase::display($media_file->asAtom());
			}
		}
		Dase::error(404);
	}

	public static function getMediaResource($params) 
	{
		Dase_Auth::authorize('read',$params);
		if (isset($params['collection_ascii_id']) && ($params['serial_number'] && $params['size'])) {
			$media_file = new Dase_DBO_MediaFile;
			$media_file->p_collection_ascii_id = $params['collection_ascii_id'];
			$media_file->p_serial_number = $params['serial_number'];
			$media_file->size = $params['size'];
			if ($media_file->findOne()) {
				//using RelativeLink due to APE problem w/ getLink
				Dase::redirect($media_file->getRelativeLink());
			}
		}
		Dase::error(404);
	}

	public static function listCollectionEntries($params) 
	{
		Dase_Auth::authorize('read',$params);
		$start = Dase_Filter::filterGet('start');
		if (!$start) {
			$start = 1;
		}
		$c = Dase_Collection::get($params);
		Dase::display($c->asAppCollection($start));

	}

	public static function deleteMediaFile($params) 
	{
		Dase_Auth::authorize('write',$params);
		//for now, only deletes the database entry
		if (isset($params['collection_ascii_id']) && ($params['serial_number'] && $params['size'])) {
			$media_file = new Dase_DBO_MediaFile;
			$media_file->p_collection_ascii_id = $params['collection_ascii_id'];
			$media_file->p_serial_number = $params['serial_number'];
			$media_file->size = $params['size'];
			if ($media_file->findOne()) {
				$media_file->delete();
				header("HTTP/1.1 200 OK");
				exit;
			}
		}
		Dase::error(500);
	}

	public static function listItemMedia($params) 
	{
		Dase_Auth::authorize('read',$params);
		if (!isset($params['collection_ascii_id']) || !isset($params['serial_number'])) {
			Dase::error(404);
		}
		$item = Dase_DBO_Item::get($params['collection_ascii_id'],$params['serial_number']);
		if (!$item) {
			Dase::error(404);
		}
		Dase::display($item->mediaAsAtomFeed());
	}

	public static function getCollectionServiceDoc($params) 
	{
		Dase_Auth::authorize('read',$params);
		$c = Dase_Collection::get($params);
		Dase::display($c->getAtompubServiceDoc());
	}

	public static function getItemServiceDoc($params) 
	{
		Dase_Auth::authorize('read',$params);
		$i = Dase_DBO_Item::get($params['collection_ascii_id'],$params['serial_number']);
		Dase::display($i->getAtompubServiceDoc());
	}

	public static function getItem($params)
	{
		Dase_Auth::authorize('read',$params);
		$item = Dase_DBO_Item::get($params['collection_ascii_id'],$params['serial_number']);
		if ($item) {
			Dase::display($item->asAppMember());
		} else {
			Dase::error(401);
		}
	}

	public static function updateItem($params)
	{
		Dase_Auth::authorize('write',$params);
		$entry = Dase_Atom_Entry_MemberItem::load("php://input");
		$metadata = "";
		if ($entry->validate()) {
			$item = $entry->replace($params);
			header("HTTP/1.1 200 Ok");
			exit;
		} else {
			//see http://www.imc.org/atom-protocol/mail-archive/msg10901.html
			Dase::error(422);
		}
	}

	public static function validate($params)
	{
		$entry = Dase_Atom_Entry::load("php://input");
		if ($entry->validate()) {
			print "valid!";
			exit;
		} else {
			//see http://www.imc.org/atom-protocol/mail-archive/msg10901.html
			Dase::error(422);
		}
	}

	public static function createItem($params)
	{
		Dase_Auth::authorize('write',$params);
		$entry = Dase_Atom_Entry_MemberItem::load("php://input",false);
		$metadata = "";
		if ($entry->validate()) {
			$item = $entry->insert($params);
			header("HTTP/1.1 201 Created");
			header("Content-Type: application/atom+xml;type=entry;charset='utf-8'");
			header("Location: ".APP_ROOT."/edit/".$params['collection_ascii_id']."/".$item->serial_number);
			Dase::display($item->asAppMember());
		} else {
			//see http://www.imc.org/atom-protocol/mail-archive/msg10901.html
			Dase::error(422);
		}
	}

	public static function deleteItem($params)
	{
		Dase_Auth::authorize('write',$params);
		$doomed = Dase_DBO_Item::get($params['collection_ascii_id'],$params['serial_number']);
		if ($doomed) {
			$doomed->expunge();
			header("HTTP/1.1 200 Ok");
			exit;
		} else {
			Dase::error(404);
		}
	}

	public static function createMediaFile($params) 
	{
		Dase_Auth::authorize('write',$params);
		if (!isset($params['collection_ascii_id']) || !isset($params['serial_number'])) {
			Dase::error(404);
		}
		$item = Dase_DBO_Item::get($params['collection_ascii_id'],$params['serial_number']);
		if (!$item) {
			Dase::error(404);
		}
		$types = array('image/*','audio/*','video/*');
		if(!isset($_SERVER['CONTENT_LENGTH']) || !isset($_SERVER['CONTENT_TYPE'])) {
			Dase::error(411);
		}
		$type = $_SERVER['CONTENT_TYPE'];
		list($type,$subtype) = explode('/',$type);
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
		$upload_dir = Dase::getConfig('path_to_media').'/'.$params['collection_ascii_id'].'/uploaded_files';
		if (!file_exists($upload_dir)) {
			Dase::error(401);
		}

		$ext = preg_replace( '|.*/([a-z0-9]+)|', '$1', $_SERVER['CONTENT_TYPE'] );
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
			$u = new Dase_Upload(Dase_File::newFile($new_file),$item->getCollection(),false); //false means do NOT check for dup
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
		$m->p_collection_ascii_id = $params['collection_ascii_id'];
		$m->p_serial_number = $params['serial_number'];
		$m->size = $u->getDaseFileSize(); //meaning media directory
		if ($m->findOne()) {
			$mle_url = APP_ROOT .'/edit/'.$m->p_collection_ascii_id.'/'.$m->p_serial_number.'/'.$m->size;
			header("Location:". $mle_url,TRUE,201);
			Dase::display($m->asAtom(),false); //false means DO NOT CACHE (important!)
		}
	}
}
