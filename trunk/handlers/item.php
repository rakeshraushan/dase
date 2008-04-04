<?php

class ItemHandler
{
	public static function asAtom($params)
	{
		if (isset($params['collection_ascii_id']) && ($params['serial_number'])) {
			$item = Dase_DBO_Item::get($params['collection_ascii_id'],$params['serial_number']);
			if ($item) {
				Dase::display($item->asAtom());
			}
		}
		Dase_Error::report(404);
	}

	public static function addMedia($params)
	{
		if (isset($params['collection_ascii_id']) && ($params['serial_number'])) {
			//from wordpress needs work!!!!!!!!!!!!!!!!!!
			$fp = fopen("php://input", "rb");
			$bits = NULL;
			while(!feof($fp)) {
				$bits .= fread($fp, 4096);
			}
			fclose($fp);

			$slug = '';
			if ( isset( $_SERVER['HTTP_SLUG'] ) )
				$slug = Dase_Util::dirify( $_SERVER['HTTP_SLUG'] );
			elseif ( isset( $_SERVER['HTTP_TITLE'] ) )
				$slug = Dase_Util::dirify( $_SERVER['HTTP_TITLE'] );
			elseif ( empty( $slug ) ) // just make a random name
				$slug = substr( md5( uniqid( microtime() ) ), 0, 7);
			$ext = preg_replace( '|.*/([a-z0-9]+)|', '$1', $_SERVER['CONTENT_TYPE'] );
			$slug = "$slug.$ext";
			$file = wp_upload_bits( $slug, NULL, $bits);

			function wp_upload_bits( $name, $deprecated, $bits, $time = NULL ) {
				if ( empty( $name ) )
					return array( 'error' => __( "Empty filename" ) );

				$wp_filetype = wp_check_filetype( $name );
				if ( !$wp_filetype['ext'] )
					return array( 'error' => __( "Invalid file type" ) );

				$upload = wp_upload_dir( $time );

				if ( $upload['error'] !== false )
					return $upload;

				$filename = wp_unique_filename( $upload['path'], $name );

				$new_file = $upload['path'] . "/$filename";
				if ( ! wp_mkdir_p( dirname( $new_file ) ) ) {
					$message = sprintf( __( 'Unable to create directory %s. Is its parent directory writable by the server?' ), dirname( $new_file ) );
					return array( 'error' => $message );
				}

				$ifp = @ fopen( $new_file, 'wb' );
				if ( ! $ifp )
					return array( 'error' => sprintf( __( 'Could not write file %s' ), $new_file ) );

				@fwrite( $ifp, $bits );
				fclose( $ifp );
				// Set correct file permissions
				$stat = @ stat( dirname( $new_file ) );
				$perms = $stat['mode'] & 0007777;
				$perms = $perms & 0000666;
				@ chmod( $new_file, $perms );

				// Compute the URL
				$url = $upload['url'] . "/$filename";

				return array( 'file' => $new_file, 'url' => $url, 'error' => false );
			}

			log_app('wp_upload_bits returns:',print_r($file,true));

			$url = $file['url'];
			$file = $file['file'];

			// Construct the attachment array
			$attachment = array(
				'post_title' => $slug,
				'post_content' => $slug,
				'post_status' => 'attachment',
				'post_parent' => 0,
				'post_mime_type' => $type,
				'guid' => $url
			);

			// Save the data
			$postID = wp_insert_attachment($attachment, $file);

			if (!$postID)
				$this->internal_error(__('Sorry, your entry could not be posted. Something wrong happened.'));

			$output = $this->get_entry($postID, 'attachment');

			$this->created($postID, $output, 'attachment');
			log_app('function',"create_attachment($postID)");
		}
	}

	public static function display($params)
	{
		if (isset($params['collection_ascii_id']) && ($params['serial_number'])) {
			//see if it exists
			if (Dase_DBO_Item::get($params['collection_ascii_id'],$params['serial_number'])) {
				$t = new Dase_Xslt;
				$t->stylesheet = XSLT_PATH.'item/transform.xsl';
				$t->set('src',APP_ROOT.'/atom/collection/'. $params['collection_ascii_id'] . '/' . $params['serial_number']);
				Dase::display($t->transform());
			} else {
				Dase_Error::report(404);
			}
		}
	}

	public static function getServiceDoc($params) 
	{
		$i = Dase_DBO_Item::get($params['collection_ascii_id'],$params['serial_number']);
		Dase::display($i->getAtompubServiceDoc());
	}

	public static function editForm($params)
		//create this!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
	{
		if (isset($params['collection_ascii_id']) && ($params['serial_number'])) {
			//see if it exists
			if (Dase_DBO_Item::get($params['collection_ascii_id'],$params['serial_number'])) {
				$t = new Dase_Xslt;
				$t->stylesheet = XSLT_PATH.'item/transform.xsl';
				$t->set('src',APP_ROOT.'/atom/collection/'. $params['collection_ascii_id'] . '/' . $params['serial_number']);
				Dase::display($t->transform());
			} else {
				Dase_Error::report(404);
			}
		}
	}
}

