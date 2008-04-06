<?php
class Dase_Atom_Pub_Media
{
	//from wordpress:
	function create_attachment() {

		$type = $this->get_accepted_content_type();

		if(!current_user_can('upload_files'))
			$this->auth_required(__('You do not have permission to upload files.'));

		$fp = fopen("php://input", "rb");
		$bits = NULL;
		while(!feof($fp)) {
			$bits .= fread($fp, 4096);
		}
		fclose($fp);

		$slug = '';
		if ( isset( $_SERVER['HTTP_SLUG'] ) )
			$slug = sanitize_file_name( $_SERVER['HTTP_SLUG'] );
		elseif ( isset( $_SERVER['HTTP_TITLE'] ) )
			$slug = sanitize_file_name( $_SERVER['HTTP_TITLE'] );
		elseif ( empty( $slug ) ) // just make a random name
			$slug = substr( md5( uniqid( microtime() ) ), 0, 7);
		$ext = preg_replace( '|.*/([a-z0-9]+)|', '$1', $_SERVER['CONTENT_TYPE'] );
		$slug = "$slug.$ext";
		$file = wp_upload_bits( $slug, NULL, $bits);

		log_app('wp_upload_bits returns:',print_r($file,true));

		$url = $file['url'];
		$file = $file['file'];

		do_action('wp_create_file_in_uploads', $file); // replicate

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

	function put_attachment($postID) {
		// checked for valid content-types (atom+xml)
		// quick check and exit
		$this->get_accepted_content_type($this->atom_content_types);

		$parser = new AtomParser();
		if(!$parser->parse()) {
			$this->bad_request();
		}

		$parsed = array_pop($parser->feed->entries);

		// check for not found
		global $entry;
		$this->set_current_entry($postID);

		if(!current_user_can('edit_post', $entry['ID']))
			$this->auth_required(__('Sorry, you do not have the right to edit this post.'));

		extract($entry);

		$post_title = $parsed->title[1];
		$post_content = $parsed->content[1];
		$pubtimes = $this->get_publish_time($parsed->updated);
		$post_modified = $pubtimes[0];
		$post_modified_gmt = $pubtimes[1];

		$postdata = compact('ID', 'post_content', 'post_title', 'post_category', 'post_status', 'post_excerpt', 'post_modified', 'post_modified_gmt');
		$this->escape($postdata);

		$result = wp_update_post($postdata);

		if (!$result) {
			$this->internal_error(__('For some strange yet very annoying reason, this post could not be edited.'));
		}

		log_app('function',"put_attachment($postID)");
		$this->ok();
	}

	function delete_attachment($postID) {
		log_app('function',"delete_attachment($postID). File '$location' deleted.");

		// check for not found
		global $entry;
		$this->set_current_entry($postID);

		if(!current_user_can('edit_post', $postID)) {
			$this->auth_required(__('Sorry, you do not have the right to delete this post.'));
		}

		$location = get_post_meta($entry['ID'], '_wp_attached_file', true);
		$filetype = wp_check_filetype($location);

		if(!isset($location) || 'attachment' != $entry['post_type'] || empty($filetype['ext']))
			$this->internal_error(__('Error ocurred while accessing post metadata for file location.'));

		// delete file
		@unlink($location);

		// delete attachment
		$result = wp_delete_post($postID);

		if (!$result) {
			$this->internal_error(__('For some strange yet very annoying reason, this post could not be deleted.'));
		}

		log_app('function',"delete_attachment($postID). File '$location' deleted.");
		$this->ok();
	}

	function get_file($postID) {

		// check for not found
		global $entry;
		$this->set_current_entry($postID);

		// then whether user can edit the specific post
		if(!current_user_can('edit_post', $postID)) {
			$this->auth_required(__('Sorry, you do not have the right to edit this post.'));
		}

		$location = get_post_meta($entry['ID'], '_wp_attached_file', true);
		$filetype = wp_check_filetype($location);

		if(!isset($location) || 'attachment' != $entry['post_type'] || empty($filetype['ext']))
			$this->internal_error(__('Error ocurred while accessing post metadata for file location.'));

		status_header('200');
		header('Content-Type: ' . $entry['post_mime_type']);
		header('Connection: close');

		$fp = fopen($location, "rb");
		while(!feof($fp)) {
			echo fread($fp, 4096);
		}
		fclose($fp);

		log_app('function',"get_file($postID)");
		exit;
	}

	function put_file($postID) {

		// first check if user can upload
		if(!current_user_can('upload_files'))
			$this->auth_required(__('You do not have permission to upload files.'));

		// check for not found
		global $entry;
		$this->set_current_entry($postID);

		// then whether user can edit the specific post
		if(!current_user_can('edit_post', $postID)) {
			$this->auth_required(__('Sorry, you do not have the right to edit this post.'));
		}

		$location = get_post_meta($entry['ID'], '_wp_attached_file', true);
		$filetype = wp_check_filetype($location);

		if(!isset($location) || 'attachment' != $entry['post_type'] || empty($filetype['ext']))
			$this->internal_error(__('Error ocurred while accessing post metadata for file location.'));

		$fp = fopen("php://input", "rb");
		$localfp = fopen($location, "w+");
		while(!feof($fp)) {
			fwrite($localfp,fread($fp, 4096));
		}
		fclose($fp);
		fclose($localfp);

		$ID = $entry['ID'];
		$pubtimes = $this->get_publish_time($entry->published);
		$post_date = $pubtimes[0];
		$post_date_gmt = $pubtimes[1];
		$pubtimes = $this->get_publish_time($parsed->updated);
		$post_modified = $pubtimes[0];
		$post_modified_gmt = $pubtimes[1];

		$post_data = compact('ID', 'post_date', 'post_date_gmt', 'post_modified', 'post_modified_gmt');
		$result = wp_update_post($post_data);

		if (!$result) {
			$this->internal_error(__('Sorry, your entry could not be posted. Something wrong happened.'));
		}

		log_app('function',"put_file($postID)");
		$this->ok();
	}

}
