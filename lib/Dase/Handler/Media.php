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
}

