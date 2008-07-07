<?php

class Dase_Handler_Media extends Dase_Handler
{
	public $resource_map = array(
		'{collection_ascii_id}/{size}/{serial_number}' => 'media_file',
	);

	protected function setup($request)
	{
		//work on ACL!!!!!!!!!!!!
	}

	public function getMediaFileJpg($request)
	{
		$collection_ascii_id = $request->get('collection_ascii_id');
		$serial_number = $request->get('serial_number');
		$size = $request->get('size');
		$request->serveFile($this->_getFilePath($collection_ascii_id,$serial_number,$size,$request->format),$request->response_mime_type);
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
		);
		$path = '/mnt/www-data/dase/media/'.
			$collection_ascii_id.'/'.
			$sizes[$size]['dir'].'/'.
			$serial_number.'.'.$format;
		return $path;
	}


	public function getMediaFileAtom($request) 
	{
		//todo: work on this
		$media_file = new Dase_DBO_MediaFile;
		$media_file->p_collection_ascii_id = $request->get('collection_ascii_id');
		$media_file->p_serial_number = $request->get('serial_number');
		$media_file->size = $request->get('size');
		if ($media_file->findOne()) {
			$request->renderResponse($media_file->asAtom());
		} else {
			$request->renderResponse(404);
		}
	}
}

