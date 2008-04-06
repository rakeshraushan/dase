<?php

class AppHandler
{
	public static function getMediaLinkEntry($params) 
	{
		if (isset($params['collection_ascii_id']) && ($params['serial_number'] && $params['size'])) {
			$media_file = new Dase_DBO_MediaFile;
			$media_file->p_collection_ascii_id = $params['collection_ascii_id'];
			$media_file->p_serial_number = $params['serial_number'];
			$media_file->size = $params['size'];
			if ($media_file->findOne()) {
				Dase::display($media_file->asAtom());
			}
		}
		Dase_Error::report(404);
	}

	public static function getMediaResource($params) 
	{
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
		Dase_Error::report(404);
	}

	public static function deleteMediaFile($params) 
	{

		//until authorization is in place!
		return;


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
		Dase_Error::report(500);
	}

	public static function listItemMedia($params) {
		if (!isset($params['collection_ascii_id']) || !isset($params['serial_number'])) {
			Dase_Error::report(404);
		}
		$item = Dase_DBO_Item::get($params['collection_ascii_id'],$params['serial_number']);
		if (!$item) {
			Dase_Error::report(404);
		}
		Dase::display($item->mediaAsAtomFeed());
	}

	public static function createMediaFile($params) 
	{
		if (!isset($params['collection_ascii_id']) || !isset($params['serial_number'])) {
			Dase_Error::report(404);
		}
		$item = Dase_DBO_Item::get($params['collection_ascii_id'],$params['serial_number']);
		if (!$item) {
			Dase_Error::report(404);
		}
		$types = array('image/*','audio/*','video/*');
		if(!isset($_SERVER['CONTENT_LENGTH']) || !isset($_SERVER['CONTENT_TYPE'])) {
			Dase_Error::report(411);
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
		$upload_dir = Dase_Config::get('path_to_media').'/'.$params['collection_ascii_id'].'/uploaded_files';
		if (!file_exists($upload_dir)) {
			Dase_Error::report(401);
		}

		$ext = preg_replace( '|.*/([a-z0-9]+)|', '$1', $_SERVER['CONTENT_TYPE'] );
		$new_file = $upload_dir.'/'.$item->serial_number.'.'.$ext;

		$ifp = @ fopen( $new_file, 'wb' );
		if (!$ifp) {
			Dase_Error::report(500);
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
			Dase_Log::put('error',$e->getMessage());
			Dase_Error::report(500);
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
